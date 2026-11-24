<?php

namespace App\Services;

use Illuminate\Support\Collection;

class StudentPointTargets
{
    public const SOFT_SKILL_TARGET = 50;
    public const HARD_SKILL_TARGET = 100;
    public const PHASE_TARGET = 8 * (self::SOFT_SKILL_TARGET + self::HARD_SKILL_TARGET);

    public function forOutcomes(Collection $outcomes): array
    {
        return $outcomes->mapWithKeys(fn ($outcome) => [$outcome->id => match ($outcome->type) {
            'Softskills' => self::SOFT_SKILL_TARGET,
            'Hardskills' => self::HARD_SKILL_TARGET,
            default => 0,
        }])->all();
    }

    public static function percentage(int $earned, int $target): float
    {
        return $target > 0 ? intdiv(min($earned, $target) * 10000, $target) / 100 : 0;
    }
}
