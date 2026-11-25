<?php

namespace App\Http\Controllers\Teamleader;

use App\Http\Controllers\RoleDashboardController;
use App\Models\LearningOutcome;
use App\Models\User;
use App\Services\LearningProgress;
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
            scripts: ['js/common.js', 'js/role-settings.js', 'js/roles/teamleider.js'],
        );
    }

    /**
     * Bereken alleen geaggregeerde cijfers voor de teamleider.
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

        return [
            'studentCount' => $studentCount,
            'averagePercentage' => $target ? (int) round(100 * $earned / $target) : 0,
            'attentionCount' => $attention,
            'phases' => $phasePercentages,
        ];
    }

}
