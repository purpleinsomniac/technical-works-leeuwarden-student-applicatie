<?php

namespace App\Http\Controllers\Teamleader;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Progress;
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
            activeRole: 'teamleider',
            view: 'teamleader.dashboard',
            roleData: $this->overview(),
            scripts: ['js/common.js', 'js/roles/teamleider.js'],
        );
    }

    /**
     * Bereken alleen geaggregeerde cijfers voor de teamleider.
     */
    private function overview(): array
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
}
