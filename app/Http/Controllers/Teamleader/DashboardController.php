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
                ->get(['id', 'naam', 'email', 'rol', 'hoofdrol_id']),
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

        $students = $group->students->map(function (User $student): array {
            $outcomes = $student->progress
                ->map(function (Progress $item): array {
                    $target = $this->numericTarget($item);
                    $percentage = $target === null
                        ? null
                        : min(100, (int) round(($item->gevalideerde_uren / $target) * 100));

                    return [
                        'code' => $item->learningOutcome->code,
                        'title' => $item->learningOutcome->titel,
                        'phase' => $item->learningOutcome->studyPhase?->naam,
                        'hours' => $item->gevalideerde_uren,
                        'target' => $target,
                        'percentage' => $percentage,
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
                'outcomes' => $outcomes,
            ];
        });

        return [
            'id' => $group->id,
            'name' => $group->naam,
            'schoolYear' => $group->schooljaar,
            'coach' => $group->coach?->naam,
            'description' => $group->omschrijving,
            'studentCount' => $students->count(),
            'averagePercentage' => (int) round($students->avg('percentage') ?? 0),
            'attentionCount' => $progressItems->whereIn('status', ['niet_gestart', 'gestart'])->count(),
            'phases' => $phasePercentages,
            'students' => $students,
        ];
    }

    private function numericTarget(Progress $progress): ?float
    {
        $target = $progress->normwaarden ?? $progress->learningOutcome?->normwaarden;

        return is_numeric($target) && (float) $target > 0 ? (float) $target : null;
    }
}
