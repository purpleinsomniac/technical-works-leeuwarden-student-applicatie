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
use App\Services\ProjectPoints;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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

    /**
     * Verzamel uitsluitend de gegevens van de ingelogde student.
     */
    private function studentData(User $user): array
    {
        // Bereken de puntenverdeling één keer voor de projecten en leeruitkomsten.
        $pointService = app(ProjectPoints::class);
        $allocation = $pointService->allocation($user);
        $projectPoints = $allocation['outcomes'];
        $coachName = User::query()
            ->where('rol', 'coach')
            ->value('naam') ?? 'Coach';

        // Combineer de algemene leeruitkomsten met de voortgang van deze student.
        $outcomes = LearningOutcome::query()
            ->with([
                'studyPhase',
                'progress' => fn ($query) => $query->where('gebruiker_id', $user->id),
            ])
            ->orderBy('code')
            ->get()
            ->map(function ($outcome) use ($projectPoints, $pointService): array {
                $progress = $outcome->progress->first();
                $hours = $projectPoints[$outcome->id] ?? 0;
                $target = $pointService->norm($outcome);
                $percentage = $target
                    ? min(100, (int) round(($hours / $target) * 100))
                    : null;

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
                    'updatedAt' => $progress?->updated_at?->locale('nl')->translatedFormat('j F Y'),
                ];
            })
            ->values();

        // Sorteer projecten op deadline, met projecten zonder deadline achteraan.
        $assignments = Assignment::query()
            ->where('gebruiker_id', $user->id)
            ->with(['learningOutcomes.studyPhase', 'files'])
            ->orderByRaw('inleverdatum is null, inleverdatum')
            ->get()
            ->map(fn (Assignment $assignment): array => $this->assignmentPayload($assignment, $allocation))
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

        // Vergelijk de behaalde punten per fase met de som van de richtwaarden.
        $phaseTotals = collect(['OF', 'VF', 'AF'])->mapWithKeys(function (string $phase) use ($outcomes): array {
            $items = $outcomes->where('phase', $phase)->whereNotNull('hours');
            $hours = (int) $items->sum('hours');
            $target = $items->sum(
                fn (array $item): float => is_numeric($item['target']) ? (float) $item['target'] : 0.0,
            );

            return [$phase => [
                'hours' => $hours,
                'percentage' => $target > 0 ? min(100, (int) round(($hours / $target) * 100)) : 0,
            ]];
        });

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
            'phases' => [
                ['OF', 'Oriëntatiefase', $phaseTotals['OF']['percentage'], $phaseTotals['OF']['hours']],
                ['VF', 'Verdiepingsfase', $phaseTotals['VF']['percentage'], $phaseTotals['VF']['hours']],
                ['AF', 'Afrondingsfase', $phaseTotals['AF']['percentage'], $phaseTotals['AF']['hours']],
            ],
            // Alleen afgeronde projecten verschijnen als puntenregistratie.
            'records' => $assignments->where('status', 'completed')->map(fn (array $assignment): array => [
                'assignmentId' => $assignment['id'],
                'title' => $assignment['title'],
                'type' => $assignment['type'],
                'description' => $assignment['description'],
                'outcomeCodes' => $assignment['outcomeCodes'],
                'phases' => $assignment['phases'],
                'hours' => $assignment['hours'],
                'year' => 1,
                'date' => $assignment['registeredAt'] ?? 'Datum onbekend',
                'dateIso' => $assignment['registeredAtIso'],
                'approver' => $coachName.' · coach',
            ])->values(),
            'hoursByYear' => [
                1 => [
                    'total' => $phaseTotals->sum('hours'),
                    'orientation' => $phaseTotals['OF']['hours'],
                    'deepening' => $phaseTotals['VF']['hours'],
                    'completion' => $phaseTotals['AF']['hours'],
                ],
                2 => ['total' => 0, 'orientation' => 0, 'deepening' => 0, 'completion' => 0],
                3 => ['total' => 0, 'orientation' => 0, 'deepening' => 0, 'completion' => 0],
                4 => ['total' => 0, 'orientation' => 0, 'deepening' => 0, 'completion' => 0],
            ],
        ];
    }

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
            'registeredAt' => $assignment->updated_at?->locale('nl')->translatedFormat('j F Y'),
            'registeredAtIso' => $assignment->updated_at?->format('Y-m-d'),
            'hours' => $allocation['projects'][$assignment->id] ?? 0,
            'maxPoints' => $allocation['maximums'][$assignment->id] ?? 0,
            'outcomeCodes' => $assignment->learningOutcomes
                ->where('studiefase_id', $firstOutcome?->studiefase_id)
                ->pluck('code')->values()->all(),
            'files' => $assignment->files->map(StudentPayload::assignmentFile(...))->values()->all(),
        ];
    }
}
