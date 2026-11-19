<?php

namespace App\Http\Controllers\Teamleader;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Group;
use App\Models\Progress;
use App\Models\StudyPhase;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends RoleDashboardController
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return $this->dashboardView(
            user: $user,
            activeRole: 'teamleider',
            view: 'teamleader.dashboard',
            style: 'css/roles/teamleader.css',
            roleData: $this->overview(),
            scripts: ['js/common.js', 'js/role-settings.js', 'js/roles/teamleider.js'],
        );
    }

    /**
     * Bereken groepscijfers en studentvoortgang voor het teamleiderdashboard.
     */
    private function overview(): array
    {
        $progress = Progress::with('learningOutcome.studyPhase')->get();
        $percentages = $progress
            ->filter(fn (Progress $item): bool => $this->numericTarget($item) !== null)
            ->map(function (Progress $item): int {
                $target = $this->numericTarget($item);

                return min(100, (int) round(($item->gevalideerde_uren / $target) * 100));
            });

        $phasePercentages = $progress
            ->groupBy(fn (Progress $item): string => $item->learningOutcome->studyPhase?->naam ?? 'Onbekend')
            ->map(function ($items): int {
                $values = $items
                    ->filter(fn (Progress $item): bool => $this->numericTarget($item) !== null)
                    ->map(function (Progress $item): int {
                        $target = $this->numericTarget($item);

                        return min(100, (int) round(($item->gevalideerde_uren / $target) * 100));
                    });

                return (int) round($values->avg() ?? 0);
            });

        $phaseNames = StudyPhase::orderBy('id')->pluck('naam');
        $groups = Group::query()
            ->with([
                'coach:id,naam',
                'studyPhase',
                'students' => fn ($query) => $query
                    ->where('rol', 'student')
                    ->with(['studyPhase', 'progress.learningOutcome.studyPhase']),
            ])
            ->orderBy('naam')
            ->orderBy('schooljaar')
            ->get()
            ->map(fn (Group $group): array => $this->groupOverview($group, $phaseNames));

        return [
            'studentCount' => User::where('rol', 'student')->count(),
            'groups' => $groups,
            'coaches' => User::query()
                ->with('primaryRole')
                ->where(function ($query): void {
                    $query->whereHas('primaryRole', fn ($roleQuery) => $roleQuery->where('naam', 'coach'))
                        ->orWhere(function ($query): void {
                            $query->whereNull('hoofdrol_id')->where('rol', 'coach');
                        });
                })
                ->orderBy('naam')
                ->get(['id', 'naam', 'email', 'rol', 'hoofdrol_id'])
                ->map(fn (User $coach): array => [
                    'id' => $coach->id,
                    'name' => $coach->naam,
                    'email' => $coach->email,
                    'accountType' => ucfirst($coach->primaryRole?->naam ?? $coach->rol),
                    'groups' => $groups
                        ->where('coachId', $coach->id)
                        ->map(fn (array $group): array => [
                            'id' => $group['id'],
                            'name' => $group['name'],
                            'studentCount' => $group['studentCount'],
                            'redStudentCount' => $group['redStudentCount'],
                            'redOrPurpleOutcomeCount' => $group['redOrPurpleOutcomeCount'],
                            'averagePercentage' => $group['averagePercentage'],
                            'displayAveragePercentage' => $group['displayAveragePercentage'],
                            'studyPhase' => $group['studyPhase'],
                            'goal' => $group['goal'],
                        ])
                        ->values(),
                ])
                ->values(),
            'averagePercentage' => (int) round($percentages->avg() ?? 0),
            'attentionCount' => $progress->whereIn('status', ['niet_gestart', 'gestart'])->count(),
            'phases' => $phasePercentages,
        ];
    }

    private function groupOverview(Group $group, Collection $phaseNames): array
    {
        $progressItems = $group->students->flatMap(fn (User $student) => $student->progress);
        $phasePercentages = $phaseNames->mapWithKeys(function (string $phase) use ($progressItems): array {
            $values = $progressItems
                ->filter(fn (Progress $item): bool => $item->learningOutcome->studyPhase?->naam === $phase)
                ->map(function (Progress $item): ?int {
                    $target = $this->numericTarget($item);

                    return $target === null
                        ? null
                        : min(100, (int) round(($item->gevalideerde_uren / $target) * 100));
                })
                ->filter(fn ($value): bool => $value !== null);

            return [$phase => (int) round($values->avg() ?? 0)];
        });
        $groupAveragePercentage = (int) round($group->students
            ->map(function (User $student): int {
                $percentages = $student->progress
                    ->map(function (Progress $item): ?int {
                        $target = $this->numericTarget($item);

                        return $target === null
                            ? null
                            : min(100, (int) round(($item->gevalideerde_uren / $target) * 100));
                    })
                    ->filter(fn ($value): bool => $value !== null);

                return (int) round($percentages->avg() ?? 0);
            })
            ->avg() ?? 0);
        $studyPhase = $group->studyPhase?->naam;
        $studyPhaseOrder = $this->phaseRank($studyPhase);
        $displayAveragePercentage = $studyPhase === null
            ? $groupAveragePercentage
            : (int) ($phasePercentages[$studyPhase] ?? $groupAveragePercentage);

        $students = $group->students->map(function (User $student) use ($group): array {
            $outcomes = $student->progress
                ->map(function (Progress $item) use ($group): array {
                    $target = $this->numericTarget($item);
                    $percentage = $target === null
                        ? null
                        : min(100, (int) round(($item->gevalideerde_uren / $target) * 100));

                    return [
                        'code' => $item->learningOutcome->code,
                        'title' => $item->learningOutcome->titel,
                        'phase' => $item->learningOutcome->studyPhase?->naam,
                        'phaseOrder' => $this->phaseRank($item->learningOutcome->studyPhase?->naam) ?? 4,
                        'hours' => $item->gevalideerde_uren,
                        'target' => $target,
                        'percentage' => $percentage,
                        'progressClass' => $this->progressColorClass(
                            $percentage,
                            $item->learningOutcome->studyPhase?->naam,
                            $group->studyPhase?->naam,
                            $group->doel,
                        ),
                        'status' => $item->status,
                    ];
                })
                ->values();
            $percentages = $outcomes->pluck('percentage')->filter(fn ($value): bool => $value !== null);
            $percentage = (int) round($percentages->avg() ?? 0);
            $studyPhase = $student->studyPhase?->naam;
            $studyPhasePercentage = $studyPhase === null
                ? $percentage
                : (int) round(
                    $outcomes
                        ->filter(fn (array $outcome): bool => $outcome['phase'] === $studyPhase)
                        ->pluck('percentage')
                        ->filter(fn ($value): bool => $value !== null)
                        ->avg() ?? 0
                );

            return [
                'id' => $student->id,
                'name' => $student->naam,
                'studyPhase' => $studyPhase,
                'percentage' => $percentage,
                'displayPercentage' => $studyPhasePercentage,
                'progressClass' => $this->progressColorClass(
                    $studyPhasePercentage,
                    $studyPhase,
                    $group->studyPhase?->naam,
                    $group->doel,
                ),
                'outcomes' => $outcomes,
            ];
        });
        $redStudentCount = $students->where('progressClass', 'teamleader-progress-low')->count();
        $redOrPurpleOutcomeCount = $students
            ->flatMap(fn (array $student) => $student['outcomes'])
            ->filter(fn (array $outcome): bool => in_array($outcome['progressClass'], [
                'teamleader-progress-low',
                'teamleader-progress-purple',
            ], true))
            ->count();

        return [
            'id' => $group->id,
            'coachId' => $group->coach_id,
            'name' => $group->naam,
            'schoolYear' => $group->schooljaar,
            'coach' => $group->coach?->naam,
            'description' => $group->omschrijving,
            'studyPhase' => $studyPhase,
            'studyPhaseOrder' => $studyPhaseOrder,
            'goal' => $group->doel,
            'studentCount' => $students->count(),
            'redStudentCount' => $redStudentCount,
            'redOrPurpleOutcomeCount' => $redOrPurpleOutcomeCount,
            'averagePercentage' => (int) round($students->avg('percentage') ?? 0),
            'displayAveragePercentage' => $displayAveragePercentage,
            'phases' => $phasePercentages,
            'students' => $students,
        ];
    }

    private function numericTarget(Progress $progress): ?float
    {
        $target = $progress->normwaarden ?? $progress->learningOutcome?->normwaarden;

        return is_numeric($target) && (float) $target > 0 ? (float) $target : null;
    }

    private function phaseRank(?string $phase): ?int
    {
        return match ($phase) {
            'Oriëntatiefase' => 1,
            'Verdiepingsfase' => 2,
            'Afrondingsfase' => 3,
            default => null,
        };
    }

    private function progressColorClass(?int $percentage, ?string $phase, ?string $groupPhase, ?int $goal): string
    {
        if ($percentage === null) {
            return 'teamleader-progress-NVT';
        }

        $phaseRank = $this->phaseRank($phase);
        $groupPhaseRank = $this->phaseRank($groupPhase);

        if ($groupPhase !== null) {
            if ($phaseRank === null || $groupPhaseRank === null) {
                return 'teamleader-progress-NVT';
            }

            if ($phaseRank < $groupPhaseRank && $percentage < 100) {
                return 'teamleader-progress-purple';
            }

            if ($phaseRank > $groupPhaseRank && $percentage > 10) {
                return 'teamleader-progress-blue';
            }

            if ($phaseRank !== $groupPhaseRank) {
                return 'teamleader-progress-NVT';
            }
        }

        if ($goal === null) {
            return 'teamleader-progress-NVT';
        }

        return $percentage >= $goal
            ? 'teamleader-progress-high'
            : ($percentage >= $goal - 15 ? 'teamleader-progress-medium' : 'teamleader-progress-low');
    }
}
