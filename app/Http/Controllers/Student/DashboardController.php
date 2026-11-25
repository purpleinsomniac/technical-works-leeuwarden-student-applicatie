<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\RoleDashboardController;
use App\Models\ApplicationSetting;
use App\Models\Assignment;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\Reflection;
use App\Models\User;
use App\Services\AcademicYear;
use App\Services\LearningProgress;
use App\Services\StudentPointTargets;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends RoleDashboardController
{
    // Start het studentdashboard met de gegevens en scripts voor deze rol.
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return $this->dashboardView(
            user: $user,
            activeRole: 'student',
            view: 'student.dashboard',
            frontendData: $this->studentData($user),
            roleData: ['applicationSettings' => ApplicationSetting::current()],
            style: 'css/roles/student.css',
            // Laad de scripts met gedeelde studentfuncties eerst. De initialisatie volgt als laatste.
            scripts: [
                'js/common.js',
                'js/roles/student/core.js',
                'js/roles/student/data.js',
                'js/roles/student/app.js',
                'js/roles/student/week-plan.js',
                'js/roles/student/dashboard.js',
                'js/roles/student/assignments.js',
                'js/roles/student/learning-outcomes.js',
                'js/roles/student/goals.js',
                'js/roles/student/hours.js',
                'js/roles/student/reflections.js',
                'js/roles/student/feedback.js',
                'js/roles/student/init.js',
            ],
        );
    }

    // Geef bijgewerkte punten terug zodat het dashboard ze opnieuw kan tonen.
    public function pointProgress(Request $request): JsonResponse
    {
        [$outcomes, , $phaseTotals, $yearPoints] = $this->pointData($request->user());

        return response()->json([
            'outcomes' => $outcomes,
            'phases' => $this->phaseRows($phaseTotals),
            'educationProgress' => $this->educationProgress($outcomes),
            'yearPoints' => $yearPoints,
        ])->header('Cache-Control', 'no-store');
    }

    // Verzamel beoordelingen, projectpunten en totalen per fase en leerjaar.
    private function pointData(User $user): array
    {
        $catalog = LearningOutcome::query()
            ->with('studyPhase')
            ->orderBy('code')
            ->get();
        $snapshot = app(LearningProgress::class)->snapshot($user, $catalog);
        $outcomes = $catalog->map(function ($outcome) use ($snapshot): array {
            $score = $snapshot['outcomes'][$outcome->id];
            $progress = $score['review'];
            $target = $score['target'];
            $hours = $score['earned'];
            $percentage = $score['percentage'];

            return [
                'id' => $outcome->id,
                'code' => $outcome->code,
                'phase' => StudentPayload::phaseCode($outcome->studyPhase?->naam),
                'kind' => $outcome->type,
                'number' => (int) preg_replace('/\D+/', '', str($outcome->code)->afterLast('_')->value()),
                'description' => $outcome->omschrijving,
                'pct' => $percentage,
                'hours' => $hours,
                'target' => $target,
                'short' => $outcome->titel,
                'status' => $hours >= $target ? 'behaald' : ($hours > 0 ? 'in_ontwikkeling' : 'niet_gestart'),
                'assessmentState' => $hours > 0 || ($progress && $progress->status !== 'niet_gestart')
                    ? 'assessed'
                    : (isset($snapshot['allocation']['startedOutcomes'][$outcome->id]) ? 'pending' : 'not_started'),
                'updatedAt' => $progress?->updated_at?->locale('nl')->translatedFormat('j F Y'),
            ];
        })
            ->values();

        $phaseTotals = [];
        foreach (['OF', 'VF', 'AF'] as $phase) {
            $earned = (int) $outcomes->where('phase', $phase)->sum('hours');
            $phaseTotals[$phase] = [
                'hours' => $earned,
                'percentage' => StudentPointTargets::percentage($earned, StudentPointTargets::PHASE_TARGET),
            ];
        }

        $allocation = $snapshot['allocation'];
        $today = now();
        $currentYear = (int) $user->leerjaar;
        $yearPoints = [];
        foreach ($allocation['registrations'] as $id => $registration) {
            $year = AcademicYear::forDate($registration['date'], $currentYear, $today);
            $phase = StudentPayload::phaseCode($registration['phase']);
            if ($year !== null) {
                $yearPoints[$year][$phase] = ($yearPoints[$year][$phase] ?? 0) + $allocation['projects'][$id];
            }
        }
        // Tel bij beoordelingen alleen punten die boven op de projectpunten zijn verdiend.
        foreach ($outcomes as $outcome) {
            $review = $snapshot['outcomes'][$outcome['id']]['review'];
            $year = AcademicYear::forDate($review?->updated_at, $currentYear, $today);
            if ($year !== null) {
                $phase = $outcome['phase'];
                $additional = $outcome['hours'] - ($allocation['outcomes'][$outcome['id']] ?? 0);
                $yearPoints[$year][$phase] = ($yearPoints[$year][$phase] ?? 0) + $additional;
            }
        }
        return [$outcomes, $allocation, $phaseTotals, $yearPoints];
    }

    // Bereken het totaal voor de hele opleiding op basis van de leeruitkomsten.
    private function educationProgress(Collection $outcomes): array
    {
        $points = (int) $outcomes->sum('hours');
        $target = 3 * StudentPointTargets::PHASE_TARGET;

        return ['points' => $points, 'target' => $target, 'percentage' => StudentPointTargets::percentage($points, $target)];
    }

    // Zet de fasetotalen in de volgorde waarin het dashboard ze verwacht.
    private function phaseRows(array $totals): array
    {
        return [
            ['OF', 'Oriëntatiefase', $totals['OF']['percentage'], $totals['OF']['hours']],
            ['VF', 'Verdiepingsfase', $totals['VF']['percentage'], $totals['VF']['hours']],
            ['AF', 'Afrondingsfase', $totals['AF']['percentage'], $totals['AF']['hours']],
        ];
    }

    // Verzamel uitsluitend de gegevens van de ingelogde student.
    private function studentData(User $user): array
    {
        [$outcomes, $allocation, $phaseTotals, $yearPoints] = $this->pointData($user);
        $today = now();
        $coachName = User::query()->where('rol', 'coach')->value('naam') ?? 'Coach';

        // Sorteer projecten op deadline, met projecten zonder deadline achteraan.
        $assignments = Assignment::query()
            ->where('gebruiker_id', $user->id)
            ->with(['learningOutcomes.studyPhase', 'files'])
            ->orderByRaw('inleverdatum is null, inleverdatum')
            ->get()
            ->map(function ($assignment) use ($allocation): array {
                /** @var Assignment $assignment */
                return $this->assignmentPayload($assignment, $allocation);
            })
            ->values();

        // Neem ook verwijderde reflecties mee, zodat de student ze kan herstellen.
        $reflections = Reflection::query()
            ->withTrashed()
            ->where('gebruiker_id', $user->id)
            ->with('learningOutcome')
            ->latest()
            ->get()
            ->map(fn ($reflection): array => StudentPayload::reflection($reflection, includeArchiveStatus: true))
            ->values();

        $feedback = Feedback::query()
            ->where('student_id', $user->id)
            ->with(['author', 'learningOutcome'])
            ->latest()
            ->get()
            ->map(StudentPayload::feedback(...))
            ->values();

        $goals = LearningGoal::query()
            ->where('gebruiker_id', $user->id)
            ->with('learningOutcome.studyPhase')
            ->latest()
            ->get()
            ->map(StudentPayload::goal(...))
            ->values();

        // Bouw maximaal drie vermeldingen op voor het overzicht van verwerkte punten.
        $hourApprovals = $outcomes
            ->filter(fn (array $outcome): bool => (int) ($outcome['hours'] ?? 0) > 0)
            ->take(3)
            ->map(function (array $outcome) use ($assignments, $coachName): array {
                $assignment = $assignments->first(
                    fn (array $item): bool => in_array($outcome['code'], $item['outcomeCodes'], true),
                );

                return [
                    'title' => $assignment['title'] ?? $outcome['short'],
                    'outcome' => $outcome['short'],
                    'code' => $outcome['code'],
                    'hours' => (int) $outcome['hours'],
                    'approver' => $coachName,
                    'approverRole' => 'Coach',
                    'date' => $outcome['updatedAt'] ?? 'Datum onbekend',
                ];
            })
            ->values();

        // Zet leerdoelen, reflecties en voortgang op volgorde van datum.
        $goalActivities = $user->learningGoals()->get()->map(fn ($goal): array => [
            'title' => 'Nieuw leerdoel · '.$goal->onderwerp,
            'date' => $goal->created_at->toIso8601String(),
        ]);
        $reflectionActivities = $user->reflections()->get()->map(fn ($reflection): array => [
            'title' => 'Reflectie toegevoegd · '.$reflection->onderwerp,
            'date' => $reflection->created_at->toIso8601String(),
        ]);
        $progressActivities = $user->progress()->get()->map(fn ($progress): array => [
            'title' => 'Voortgang bijgewerkt',
            'date' => $progress->updated_at->toIso8601String(),
        ]);
        $activities = $goalActivities->concat($reflectionActivities)
            ->concat($progressActivities)
            ->sortByDesc('date')
            ->values();

        return [
            'outcomes' => $outcomes,
            'assignments' => $assignments,
            'reflections' => $reflections,
            'feedback' => $feedback,
            'goals' => $goals,
            'goalInsights' => ['activities' => $activities],
            'hourApprovals' => $hourApprovals,
            'phases' => $this->phaseRows($phaseTotals),
            'educationProgress' => $this->educationProgress($outcomes),
            'currentPhase' => StudentPayload::phaseCode($user->studyPhase?->naam),
            'phasePointTarget' => StudentPointTargets::PHASE_TARGET,
            'yearPoints' => $yearPoints,
            'pointProgressUrl' => route('student.points.progress', absolute: false),
            // Alleen afgeronde projecten verschijnen als puntenregistratie.
            'records' => $assignments->where('status', 'completed')->map(fn (array $assignment): array => [
                'assignmentId' => $assignment['id'],
                'title' => $assignment['title'],
                'type' => $assignment['type'],
                'description' => $assignment['description'],
                'outcomeCodes' => $assignment['outcomeCodes'],
                'phases' => $assignment['phases'],
                'hours' => $assignment['hours'],
                'year' => AcademicYear::forDate($allocation['registrations'][$assignment['id']]['date'] ?? null, (int) $user->leerjaar, $today),
                'date' => $assignment['registeredAt'] ?? 'Datum onbekend',
                'dateIso' => $assignment['registeredAtIso'],
                'approver' => $coachName.' · coach',
            ])->values(),
            'hoursByYear' => collect([1, 2, 3, 4])->mapWithKeys(function (int $year) use ($yearPoints): array {
                $points = $yearPoints[$year] ?? [];
                return [$year => [
                    'total' => array_sum($points),
                    'orientation' => $points['OF'] ?? 0,
                    'deepening' => $points['VF'] ?? 0,
                    'completion' => $points['AF'] ?? 0,
                ]];
            })->all(),
        ];
    }

    // Zet het project en de gekoppelde gegevens om naar de velden voor het dashboard.
    private function assignmentPayload(Assignment $assignment, array $allocation): array
    {
        // Alleen leeruitkomsten uit de fase van de eerste koppeling horen bij dit project.
        $firstOutcome = $assignment->learningOutcomes->sortBy('id')->first();
        $phase = StudentPayload::phaseCode($firstOutcome?->studyPhase?->naam);

        return [
            'id' => $assignment->id,
            'title' => $assignment->onderwerp,
            'type' => $assignment->type,
            'description' => $assignment->omschrijving,
            'status' => $assignment->status,
            'phase' => $phase,
            'phases' => $firstOutcome ? [$phase] : [],
            'dueDate' => $assignment->inleverdatum?->locale('nl')->translatedFormat('j F Y'),
            'dueDateIso' => $assignment->inleverdatum?->format('Y-m-d'),
            'registeredAt' => ($assignment->completed_at ?? $assignment->updated_at)?->locale('nl')->translatedFormat('j F Y'),
            'registeredAtIso' => ($assignment->completed_at ?? $assignment->updated_at)?->format('Y-m-d'),
            'hours' => $allocation['projects'][$assignment->id] ?? 0,
            'maxPoints' => $allocation['maximums'][$assignment->id] ?? 0,
            'outcomeCodes' => $assignment->learningOutcomes
                ->where('studiefase_id', $firstOutcome?->studiefase_id)
                ->pluck('code')->values()->all(),
            'files' => $assignment->files->map(StudentPayload::assignmentFile(...))->values()->all(),
        ];
    }
}
