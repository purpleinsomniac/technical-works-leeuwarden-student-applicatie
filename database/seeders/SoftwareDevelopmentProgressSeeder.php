<?php

namespace Database\Seeders;

use App\Models\LearningOutcome;
use App\Models\User;
use App\Services\ProjectPoints;
use App\Services\StudentPointTargets;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SoftwareDevelopmentProgressSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $student = User::where('email', 'student@example.test')->where('rol', 'student')->firstOrFail();
        $outcomes = LearningOutcome::with('studyPhase')->orderBy('code')->get();
        $service = app(ProjectPoints::class);
        $targets = app(StudentPointTargets::class)->forOutcomes($outcomes);

        // Voorbeeldniveau per vaardigheid: van oefenen tot zelfstandig uitvoeren.
        $hard = [94, 82, 63, 71, 48, 59, 89, 37];
        $soft = [66, 46, 90, 80, 58, 69, 52, 85];
        $phaseAdjustment = ['OF' => 0, 'VF' => -5, 'AF' => -10];
        DB::transaction(function () use ($student, $outcomes, $targets, $service, $hard, $soft, $phaseAdjustment): void {
            // Verdeel de 80 punten van het bestaande demoproject opnieuw in tientallen.
            $projects = $student->assignments()->where('status', 'completed')
                ->where('gevalideerde_uren', 80)->with('learningOutcomes')->get();
            foreach ($projects as $project) {
                $linked = $project->learningOutcomes->sortBy('id')->values();
                if ($linked->map(fn ($item) => (int) $item->pivot->max_points)->sort()->values()->all() !== [15, 15, 15, 15, 20]) {
                    continue;
                }
                foreach ([20, 10, 20, 10, 20] as $index => $points) {
                    $project->learningOutcomes()->updateExistingPivot($linked[$index]->id, ['max_points' => $points]);
                }
            }

            $projectPoints = $service->allocation($student, $targets)['outcomes'];
            foreach ($outcomes as $outcome) {
                if (! preg_match('/^IT_(OF|VF|AF)_LU_(Hard|Soft)skills_00([1-8])$/', $outcome->code, $parts)) {
                    continue;
                }
                $target = $targets[$outcome->id] ?? 0;
                if ($target < 1) {
                    continue;
                }
                $index = (int) $parts[3] - 1;
                $percent = ($parts[2] === 'Hard' ? $hard : $soft)[$index] + $phaseAdjustment[$parts[1]];
                $wanted = min(80, intdiv($target, 10) * 10, (int) round($target * $percent / 1000) * 10);
                $fromProjects = $projectPoints[$outcome->id] ?? 0;
                // Vul de projectpunten aan met voorbeeldbeoordelingen zonder ze dubbel te tellen.
                $validated = intdiv(max(0, $wanted - $fromProjects), 10) * 10;
                $earned = $fromProjects + $validated;
                $student->progress()->updateOrCreate(
                    ['leeruitkomst_id' => $outcome->id],
                    [
                        'gevalideerde_uren' => $validated,
                        'status' => $earned >= $target ? 'behaald' : 'in_ontwikkeling',
                    ],
                );
            }
        });
    }
}
