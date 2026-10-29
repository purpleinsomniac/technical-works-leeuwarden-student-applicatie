<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Assignment;
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
            style: 'css/roles/student.css',
            scripts: [
                'js/roles/student/core.js',
                'js/roles/student/data.js',
                'js/roles/student/app.js',
                'js/roles/student/dashboard.js',
                'js/roles/student/assignments.js',
                'js/roles/student/learning-outcomes.js',
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
            ->map(function ($outcome): array {
                $progress = $outcome->progress->first();
                $hours = $progress?->gevalideerde_uren;
                $target = $progress?->normuren ?? $outcome->normuren;
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
                    'status' => $progress?->status ?? 'niet_gestart',
                    'updatedAt' => $progress?->updated_at?->locale('nl')->translatedFormat('j F Y'),
                ];
            })
            ->values();

        $assignments = Assignment::query()
            ->where('gebruiker_id', $user->id)
            ->with(['learningOutcomes.studyPhase'])
            ->orderByRaw('inleverdatum is null, inleverdatum')
            ->get()
            ->map(fn ($assignment): array => [
                'id' => $assignment->id,
                'title' => $assignment->onderwerp,
                'type' => $assignment->type,
                'description' => $assignment->omschrijving,
                'status' => $assignment->status,
                'phase' => $this->phaseCode($assignment->learningOutcomes->first()?->studyPhase?->naam),
                'dueDate' => $assignment->inleverdatum?->locale('nl')->translatedFormat('j F Y'),
                'hours' => $assignment->gevalideerde_uren,
                'outcomeCodes' => $assignment->learningOutcomes->pluck('code')->values()->all(),
            ])
            ->values();

        $reflections = Reflection::query()
            ->where('gebruiker_id', $user->id)
            ->with('learningOutcome')
            ->latest()
            ->get()
            ->map(fn ($reflection): array => [
                'id' => (string) $reflection->id,
                'title' => $reflection->onderwerp,
                'code' => $reflection->learningOutcome->code,
                'situation' => $reflection->situatie,
                'good' => $reflection->wat_ging_goed,
                'improve' => $reflection->wat_kan_beter,
                'next' => $reflection->volgende_stap,
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
            ])
            ->values();

        $phaseTotals = collect(['OF', 'VF', 'AF'])->mapWithKeys(function (string $phase) use ($outcomes): array {
            $items = $outcomes->where('phase', $phase)->whereNotNull('hours');
            $hours = (int) $items->sum('hours');
            $target = (int) $items->sum('target');

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
            'hourApprovals' => $hourApprovals,
            'phases' => [
                ['OF', 'Oriëntatiefase', $phaseTotals['OF']['percentage'], $phaseTotals['OF']['hours']],
                ['VF', 'Verdiepingsfase', $phaseTotals['VF']['percentage'], $phaseTotals['VF']['hours']],
                ['AF', 'Afrondingsfase', $phaseTotals['AF']['percentage'], $phaseTotals['AF']['hours']],
            ],
            'records' => $assignments->map(fn (array $assignment): array => [
                $assignment['title'],
                $assignment['type'],
                $assignment['outcomeCodes'][0] ?? '',
                $assignment['hours'],
                1,
                $assignment['dueDate'] ?? 'Datum onbekend',
                $coachName.' · coach',
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
