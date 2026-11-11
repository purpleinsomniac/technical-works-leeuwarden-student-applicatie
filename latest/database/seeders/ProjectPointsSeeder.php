<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectPointsSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('email', 'student@example.test')->first();
        if (!$student) return;
        foreach ($student->assignments()->where('status', 'completed')->with('learningOutcomes.studyPhase')->get() as $project) {
            $phase = $project->learningOutcomes->sortBy('id')->first()?->studyPhase?->naam;
            $points = match ($phase) {
                'Oriëntatiefase' => 120,
                'Verdiepingsfase' => 160,
                'Afrondingsfase' => 240,
                default => null,
            };
            if ($points !== null) {
                $project->timestamps = false;
                $project->update(['gevalideerde_uren' => $points]);
            }
        }
    }
}
