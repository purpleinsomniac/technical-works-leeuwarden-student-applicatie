<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\Progress;
use App\Models\Reflection;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Retrieve the currently authenticated user and eagerly load the app state.
        $user = $request->user();
        $user?->loadMissing('appState');
        $initialen = $user
            ? collect(preg_split('/\s+/', trim($user->naam)))
                ->filter()
                ->take(2)
                ->map(fn (string $naamdeel): string => mb_strtoupper(mb_substr($naamdeel, 0, 1)))
                ->implode('')
            : '';

        // Map the user's role to the corresponding Blade view.
        $roleView = match ($user?->rol) {
            'student' => 'roles.student',
            'coach' => 'roles.coach',
            'teamleider' => 'roles.teamleider',
            'beheerder' => 'roles.beheerder',
            default => null,
        };

        // Load the role-specific frontend assets for the dashboard shell.
        $roleAssets = match ($user?->rol) {
            'student' => [
                'style' => 'css/roles/student.css',
                'scripts' => ['js/roles/student.js'],
            ],
            'coach' => [
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/coach.js'],
            ],
            'teamleider' => [
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/teamleider.js'],
            ],
            'beheerder' => [
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/beheerder.js'],
            ],
            default => [
                'style' => null,
                'scripts' => [],
            ],
        };

        // Pass the resolved role, user data, and app state to the shared app template.
        return view('app', [
            'user' => $user,
            'roleView' => $roleView,
            'roleAssets' => $roleAssets,
            'frontendUser' => $user ? [
                'id' => $user->id,
                'name' => $user->naam,
                'role' => $user->rol,
                'initials' => $initialen,
            ] : null,
            'frontendState' => $user?->appState?->gegevens ?? [],
            'frontendData' => $user?->rol === 'student' ? $this->studentData($user) : [],
            'roleData' => match ($user?->rol) {
                'coach' => $this->coachData(),
                'teamleider' => $this->teamleaderData(),
                'beheerder' => $this->administratorData(),
                default => [],
            },
        ]);
    }

    /**
     * Onderwijsgegevens voor één ingelogde student.
     */
    private function studentData(User $user): array
    {
        $outcomes = LearningOutcome::query()
            ->with([
                'studyPhase',
                'progress' => fn ($query) => $query->where('gebruiker_id', $user->id),
            ])
            ->orderBy('code')
            ->get()
            ->map(function (LearningOutcome $outcome): array {
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
                ];
            })
            ->values();

        $assignments = Assignment::query()
            ->where('gebruiker_id', $user->id)
            ->with(['learningOutcomes.studyPhase'])
            ->orderByRaw('inleverdatum is null, inleverdatum')
            ->get()
            ->map(fn (Assignment $assignment): array => [
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
            ->map(fn (Reflection $reflection): array => [
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
            ->map(fn (Feedback $item): array => [
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
            ->map(fn (LearningGoal $goal): array => [
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

        return [
            'outcomes' => $outcomes,
            'assignments' => $assignments,
            'reflections' => $reflections,
            'feedback' => $feedback,
            'goals' => $goals,
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

    private function coachData(): array
    {
        return User::query()
            ->where('rol', 'student')
            ->with(['studyPhase', 'progress.learningOutcome'])
            ->orderBy('naam')
            ->get()
            ->map(fn (User $student): array => [
                'id' => $student->id,
                'name' => $student->naam,
                'phase' => $student->studyPhase?->naam,
                'hours' => $student->progress->sum('gevalideerde_uren'),
                'outcomes' => $student->progress->map(fn ($progress): array => [
                    'id' => $progress->learningOutcome->id,
                    'title' => $progress->learningOutcome->titel,
                    'code' => $progress->learningOutcome->code,
                    'hours' => $progress->gevalideerde_uren,
                    'target' => $progress->normuren ?? $progress->learningOutcome->normuren,
                    'status' => $progress->status,
                ])->values(),
            ])
            ->values()
            ->all();
    }

    private function teamleaderData(): array
    {
        $progress = Progress::with('learningOutcome.studyPhase')->get();
        $percentages = $progress
            ->filter(fn (Progress $item): bool => (int) ($item->normuren ?? $item->learningOutcome->normuren) > 0)
            ->map(function (Progress $item): int {
                $target = (int) ($item->normuren ?? $item->learningOutcome->normuren);

                return min(100, (int) round(($item->gevalideerde_uren / $target) * 100));
            });

        $phasePercentages = $progress
            ->groupBy(fn (Progress $item): string => $item->learningOutcome->studyPhase?->naam ?? 'Onbekend')
            ->map(function ($items): int {
                $values = $items
                    ->filter(fn (Progress $item): bool => (int) ($item->normuren ?? $item->learningOutcome->normuren) > 0)
                    ->map(function (Progress $item): int {
                        $target = (int) ($item->normuren ?? $item->learningOutcome->normuren);

                        return min(100, (int) round(($item->gevalideerde_uren / $target) * 100));
                    });

                return (int) round($values->avg() ?? 0);
            });

        return [
            'studentCount' => User::where('rol', 'student')->count(),
            'averagePercentage' => (int) round($percentages->avg() ?? 0),
            'attentionCount' => $progress->whereIn('status', ['niet_gestart', 'gestart'])->count(),
            'phases' => $phasePercentages,
        ];
    }

    private function administratorData(): array
    {
        return [
            'userCount' => User::count(),
            'studentCount' => User::where('rol', 'student')->count(),
            'progressCount' => Progress::count(),
            'assignmentCount' => Assignment::count(),
            'outcomeCount' => LearningOutcome::count(),
            'lastProgressUpdate' => Progress::max('updated_at'),
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
