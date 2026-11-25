<?php

namespace App\Services;

use Carbon\CarbonInterface;

class AcademicYear
{
    public static function forDate(?CarbonInterface $date, ?int $currentYear, CarbonInterface $today): ?int
    {
        if (! $date || ! in_array($currentYear, [1, 2, 3, 4], true)) {
            return null;
        }

        $academicStart = static fn (CarbonInterface $day): int => $day->year - ($day->month < 9 ? 1 : 0);
        $year = $currentYear + $academicStart($date) - $academicStart($today);

        return $year >= 1 && $year <= $currentYear ? $year : null;
    }
}
