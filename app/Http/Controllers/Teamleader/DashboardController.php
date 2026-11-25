<?php

namespace App\Http\Controllers\Teamleader;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Group;
use App\Models\LearningOutcome;
use App\Models\Progress;
use App\Models\StudyPhase;
use App\Models\User;
use App\Services\LearningProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends RoleDashboardController
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return $this->renderPage($user, 'overview');
    }

    public function group(Request $request, int $group): View
    {
        return $this->renderPage($request->user(), 'group', $group);
    }

    public function student(Request $request, int $group, int $student): View
    {
        return $this->renderPage($request->user(), 'student', $group, $student);
    }

    private function renderPage(User $user, string $page, ?int $groupId = null, ?int $studentId = null): View
    {
        $roleData = $this->overview();
        $selectedGroup = $groupId === null
            ? null
            : $roleData['groups']->firstWhere('id', $groupId);
        abort_if($groupId !== null && $selectedGroup === null, 404);

        $selectedStudent = $studentId === null
            ? null
            : collect($selectedGroup['students'])->firstWhere('id', $studentId);
        abort_if($studentId !== null && $selectedStudent === null, 404);

        if ($selectedGroup !== null) {
            $selectedGroup['students'] = $selectedStudent === null
                ? collect($selectedGroup['students'])
                : collect([$selectedStudent]);
            $roleData['groups'] = collect([$selectedGroup]);
        }

        return $this->dashboardView(
            user: $user,
            activeRole: 'teamleider',
            view: 'teamleader.dashboard',
            style: 'css/roles/teamleader.css',
            roleData: $roleData,
            scripts: ['js/common.js', 'js/role-settings.js', 'js/roles/teamleider.js'],
            extraData: [
                'teamleaderPage' => $page,
                'teamleaderGroup' => $selectedGroup
                    ? ['id' => $selectedGroup['id'], 'name' => $selectedGroup['name']]
                    : null,
                'teamleaderStudent' => $selectedStudent
                    ? ['id' => $selectedStudent['id'], 'name' => $selectedStudent['name']]
                    : null,
            ],
        );
    }

    /**
     * Bereken groepscijfers en studentvoortgang voor het teamleiderdashboard.
     */
    private function overview(): array
    {
        $catalog = LearningOutcome::with('studyPhase')->get();
        $service = app(LearningProgress::class);
        $totals = [];
        $earned = $target = $attention = $studentCount = 0;

        foreach (User::where('rol', 'student')->lazyById() as $student) {
            $studentCount++;
            $scores = $service->snapshot($student, $catalog)['outcomes'];
            foreach ($catalog as $outcome) {
                $score = $scores[$outcome->id];
                $phase = $outcome->studyPhase?->naam ?? 'Onbekend';
                $totals[$phase]['earned'] = ($totals[$phase]['earned'] ?? 0) + $score['earned'];
                $totals[$phase]['target'] = ($totals[$phase]['target'] ?? 0) + $score['target'];
                $earned += $score['earned'];
                $target += $score['target'];
                if ($score['target'] > 0 && $score['percentage'] < 55 && ($score['review'] || $score['earned'] > 0)) {
                    $attention++;
                }
            }
        }

        $phasePercentages = array_map(fn ($row) => $row['target'] ? (int) round(100 * $row['earned'] / $row['target']) : 0, $totals);

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
            'studentCount' => $studentCount,
            'averagePercentage' => $target ? (int) round(100 * $earned / $target) : 0,
            'attentionCount' => $attention,
            'groups' => $groups,
            'coaches' => User::query()
                ->where(function ($query): void {
                    $query->whereHas('primaryRole', fn ($roleQuery) => $roleQuery->where('naam', 'coach'))
                        ->orWhere(function ($query): void {
                            $query->whereNull('hoofdrol_id')->where('rol', 'coach');
                        });
                })
                ->orderBy('naam')
                ->get(['id', 'naam', 'email', 'rol', 'hoofdrol_id'])
                ->map(function (User $coach) use ($groups): array {
                    $coachGroups = $groups->where('coachId', $coach->id);

                    return [
                        'id' => $coach->id,
                        'name' => $coach->naam,
                        'email' => $coach->email,
                        'groupStatusCounts' => collect(['green', 'yellow', 'red', 'gray'])
                            ->mapWithKeys(fn (string $color): array => [
                                $color => $coachGroups->where('attentionColor', $color)->count(),
                            ]),
                        'groups' => $coachGroups
                            ->map(fn (array $group): array => [
                                'id' => $group['id'],
                                'name' => $group['name'],
                                'studentCount' => $group['studentCount'],
                                'redStudentCount' => $group['redStudentCount'],
                                'redOutcomeCount' => $group['redOutcomeCount'],
                                'averagePercentage' => $group['averagePercentage'],
                                'displayAveragePercentage' => $group['displayAveragePercentage'],
                                'studyPhase' => $group['studyPhase'],
                                'goal' => $group['goal'],
                                'attentionColor' => $group['attentionColor'],
                            ])
                            ->values(),
                    ];
                })
                ->values(),
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
                        'kind' => $item->learningOutcome->type,
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
        $redOutcomeCount = $students
            ->flatMap(fn (array $student) => $student['outcomes'])
            ->where('progressClass', 'teamleader-progress-low')
            ->count();
        $studentCount = $students->count();
        $redStudentPercentage = $studentCount > 0 ? ($redStudentCount / $studentCount) * 100 : 0;
        $studentStatus = $redStudentCount === 0
            ? 'teamleader-progress-high'
            : ($redStudentPercentage <= 20 ? 'teamleader-progress-medium' : 'teamleader-progress-low');
        $outcomeStatus = $redOutcomeCount === 0
            ? 'teamleader-progress-high'
            : ($redOutcomeCount <= 3 * $studentCount ? 'teamleader-progress-medium' : 'teamleader-progress-low');
        $progressStatus = $group->doel === null
            ? 'teamleader-progress-NVT'
            : ($displayAveragePercentage >= $group->doel
                ? 'teamleader-progress-high'
                : ($displayAveragePercentage >= $group->doel - 15
                    ? 'teamleader-progress-medium'
                    : 'teamleader-progress-low'));
        $attentionScore = collect([$studentStatus, $outcomeStatus, $progressStatus])
            ->sum(fn (string $status): int => match ($status) {
                'teamleader-progress-low' => 3,
                'teamleader-progress-medium' => 1,
                default => 0,
            });
        $attentionColor = $studentCount === 0 || $studyPhase === null || $group->doel === null
            ? 'gray'
            : ($attentionScore >= 6 ? 'red' : ($attentionScore >= 2 ? 'yellow' : 'green'));

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
            'studentCount' => $studentCount,
            'redStudentCount' => $redStudentCount,
            'redOutcomeCount' => $redOutcomeCount,
            'attentionColor' => $attentionColor,
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
