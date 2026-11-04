<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\LearningOutcome;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolPointsSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('email','student@example.test')->first();
        if (!$student) return;
        DB::transaction(function () use ($student) {
            // De legacy 80%-norm van 50 staat gelijk aan 40 vereiste punten.
            LearningOutcome::query()->update(['normuren'=>50]);
            $projects = $student->assignments()->with('learningOutcomes')->orderBy('id')->get();
            $groups = $projects->groupBy(fn ($project) => $project->learningOutcomes->sortBy('id')->first()?->studiefase_id);
            foreach ($groups as $phase => $phaseProjects) {
                if (!$phase) continue;
                $outcomes = LearningOutcome::where('studiefase_id',$phase)->orderBy('code')->get()->values();
                foreach ($phaseProjects->values() as $index => $project) {
                    $links = [];
                    // Vier vaardigheden per project, twintig punten per vaardigheid.
                    // Na vier projecten volgt een tweede toetsronde op dezelfde vaardigheden.
                    for ($i = 0; $i < min(4,$outcomes->count()); $i++) {
                        $outcome = $outcomes[($index * 4 + $i) % $outcomes->count()];
                        $links[$outcome->id] = ['max_points'=>20];
                    }
                    $project->learningOutcomes()->sync($links);
                    $project->timestamps = false;
                    $project->update(['gevalideerde_uren'=>$project->status === 'completed' ? 80 : min(40,$project->gevalideerde_uren)]);
                }
            }
        });
    }
}
