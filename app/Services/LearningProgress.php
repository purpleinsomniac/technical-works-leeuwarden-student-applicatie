<?php

namespace App\Services;

use App\Models\LearningOutcome;
use App\Models\User;
use Illuminate\Support\Collection;

class LearningProgress
{
    public function snapshot(User $student, ?Collection $catalog = null): array
    {
        $catalog ??= LearningOutcome::with('studyPhase')->orderBy('code')->get();
        $targets = app(StudentPointTargets::class)->forOutcomes($catalog);
        $allocation = app(ProjectPoints::class)->allocation($student, $targets);
        $reviews = $student->progress()->get()->keyBy('leeruitkomst_id');
        $outcomes = [];

        foreach ($catalog as $outcome) {
            $target = $targets[$outcome->id] ?? 0;
            $review = $reviews->get($outcome->id);
            $projects = $allocation['outcomes'][$outcome->id] ?? 0;
            $earned = min($target, $projects + max(0, (int) $review?->gevalideerde_uren));
            $outcomes[$outcome->id] = [
                'target' => $target,
                'earned' => $earned,
                'percentage' => StudentPointTargets::percentage($earned, $target),
                'review' => $review,
            ];
        }

        return ['outcomes' => $outcomes, 'allocation' => $allocation];
    }
}
