<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Assignment;
use App\Models\ApplicationSetting;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\Reflection;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends RoleDashboardController
{
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
            scripts: [
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
        $pointService = app(\App\Services\ProjectPoints::class);
        $allocation = $pointService->allocation($user);
        $projectPoints = $allocation['outcomes'];
        $coachName = User::query()
            ->where('rol', 'coach')
            ->value('naam') ?? 'Coach';

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
                $percentage = $hours !== null && $target
                    ? min(100, (int) round(($hours / $target) * 100))
                    : null;

                return [
                    'id' => $outcome->id,
                    'code' => $outcome->code,
                    'phase' => $this->phaseCode($outcome->studyPhase?->naam),
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

        $assignments = Assignment::query()
            ->where('gebruiker_id', $user->id)
            ->with(['learningOutcomes.studyPhase', 'files'])
            ->orderByRaw('inleverdatum is null, inleverdatum')
            ->get()
            ->map(fn ($assignment): array => [
                'id' => $assignment->id,
                'title' => $assignment->onderwerp,
                'type' => $assignment->type,
                'description' => $assignment->omschrijving,
                'status' => $assignment->status,
                'phase' => $this->phaseCode($assignment->learningOutcomes->sortBy('id')->first()?->studyPhase?->naam),
                'phases' => $assignment->learningOutcomes
                    ->sortBy('id')->take(1)
                    ->map(fn ($outcome): string => $this->phaseCode($outcome->studyPhase?->naam))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
                'dueDate' => $assignment->inleverdatum?->locale('nl')->translatedFormat('j F Y'),
                'dueDateIso' => $assignment->inleverdatum?->format('Y-m-d'),
                'registeredAt' => $assignment->updated_at?->locale('nl')->translatedFormat('j F Y'),
                'registeredAtIso' => $assignment->updated_at?->format('Y-m-d'),
                'hours' => $allocation['projects'][$assignment->id] ?? 0,
                'maxPoints' => $allocation['maximums'][$assignment->id] ?? 0,
                'outcomeCodes' => $assignment->learningOutcomes->where('studiefase_id', $assignment->learningOutcomes->sortBy('id')->first()?->studiefase_id)->pluck('code')->values()->all(),
                'files' => $assignment->files->map(fn ($file): array => [
                    'id' => $file->id,
                    'name' => $file->originele_naam,
                    'size' => $file->bestandsgrootte,
                    'mime' => $file->mime_type,
                    'downloadUrl' => route('assignments.files.download', $file),
                    'uploadedAt' => $file->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i'),
                ])->values()->all(),
            ])
            ->values();

        $reflections = Reflection::query()
            ->withTrashed()
            ->where('gebruiker_id', $user->id)
            ->with('learningOutcome.studyPhase')
            ->latest()
            ->get()
            ->map(fn ($reflection): array => [
                'id' => (string) $reflection->id,
                'title' => $reflection->onderwerp,
                'assignmentId' => $reflection->opdracht_id,
                'code' => $reflection->learningOutcome->code,
                'situation' => $reflection->situatie,
                'good' => $reflection->wat_ging_goed,
                'improve' => $reflection->wat_kan_beter,
                'next' => $reflection->volgende_stap,
                'deleted' => $reflection->trashed(),
                'date' => $reflection->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i'),
            ])
            ->values();

        $feedback = Feedback::query()
            ->where('student_id', $user->id)
            ->with(['author', 'learningOutcome'])
            ->latest()
            ->get()
            ->map(fn ($item): array => [
                'id' => (string) $item->id,
                'type' => $item->type,
                'person' => $item->persoon ?: $item->author->naam,
                'title' => $item->onderwerp,
                'code' => $item->learningOutcome->code,
                'positive' => $item->wat_ging_goed,
                'improve' => $item->wat_kan_beter,
                'action' => $item->volgende_stap,
                'date' => $item->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i'),
            ])
            ->values();

        $goals = LearningGoal::query()
            ->where('gebruiker_id', $user->id)
            ->with('learningOutcome')
            ->latest()
            ->get()
            ->map(fn ($goal): array => [
                'id' => $goal->id,
                'title' => $goal->onderwerp,
                'description' => $goal->omschrijving,
                'status' => $goal->status,
                'code' => $goal->learningOutcome?->code,
                'outcome' => $goal->learningOutcome?->titel,
                'phaseCode' => $this->phaseCode($goal->learningOutcome?->studyPhase?->naam),
                'phase' => $goal->learningOutcome?->studyPhase?->naam,
            ])
            ->values();

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

        return [
            'outcomes' => $outcomes,
            'assignments' => $assignments,
            'reflections' => $reflections,
            'feedback' => $feedback,
            'goals' => $goals,
            'goalInsights' => [
                'activities' => $user->learningGoals()->get()->map(fn ($goal) => ['title'=>'Nieuw leerdoel · '.$goal->onderwerp,'date'=>$goal->created_at->toIso8601String()])
                    ->concat($user->reflections()->get()->map(fn ($item) => ['title'=>'Reflectie toegevoegd · '.$item->onderwerp,'date'=>$item->created_at->toIso8601String()]))
                    ->concat($user->progress()->get()->map(fn ($item) => ['title'=>'Voortgang bijgewerkt','date'=>$item->updated_at->toIso8601String()]))
                    ->sortByDesc('date')->values(),
            ],
            'hourApprovals' => $hourApprovals,
            'phases' => [
                ['OF', 'Oriëntatiefase', $phaseTotals['OF']['percentage'], $phaseTotals['OF']['hours']],
                ['VF', 'Verdiepingsfase', $phaseTotals['VF']['percentage'], $phaseTotals['VF']['hours']],
                ['AF', 'Afrondingsfase', $phaseTotals['AF']['percentage'], $phaseTotals['AF']['hours']],
            ],
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

    private function phaseCode(?string $name): string
    {
        return match ($name) {
            'Verdiepingsfase' => 'VF',
            'Afrondingsfase' => 'AF',
            default => 'OF',
        };
    }
}
