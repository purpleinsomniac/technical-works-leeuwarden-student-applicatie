<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HalfYearDemoSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('email','student@example.test')->first();
        if (!$student) return;
        $keep = ['Werkplek installeren','Servicedesk als team','Probleem analyseren en advies geven','Securityscan van een werkplek','Persoonlijk ontwikkelportfolio','Netwerkdocumentatie','Prototype presenteren aan opdrachtgever'];
        DB::transaction(function () use ($student,$keep) {
            $examples = [
                ['Persoonlijk ontwikkelportfolio','Oriëntatiefase',0,'Reflectieopdracht','Bundel je bewijsstukken en reflecteer op je eerste schoolperiode.'],
                ['Netwerkdocumentatie','Verdiepingsfase',0,'Documentatieopdracht','Breng een netwerk in kaart en documenteer de inrichting en beheerafspraken.'],
                ['Prototype presenteren aan opdrachtgever','Verdiepingsfase',4,'Presentatieopdracht','Presenteer je prototype en onderbouw de gemaakte technische keuzes.'],
            ];
            foreach ($examples as [$title,$phase,$offset,$type,$description]) {
                $project = $student->assignments()->firstOrCreate(['onderwerp'=>$title], [
                    'omschrijving'=>$description,'type'=>$type,'status'=>'completed','gevalideerde_uren'=>80,
                ]);
                $outcomes = \App\Models\LearningOutcome::whereHas('studyPhase',fn ($query) => $query->where('naam',$phase))->orderBy('code')->skip($offset)->take(4)->get();
                $project->learningOutcomes()->sync($outcomes->mapWithKeys(fn ($outcome) => [$outcome->id=>['max_points'=>20]])->all());
                if ($phase === 'Verdiepingsfase') {
                    $weights = $title === 'Netwerkdocumentatie'
                        ? ['Hardskills_001'=>20,'Hardskills_002'=>20,'Hardskills_005'=>20,'Softskills_001'=>10,'Softskills_003'=>10]
                        : ['Hardskills_004'=>15,'Hardskills_006'=>15,'Softskills_001'=>20,'Softskills_003'=>15,'Softskills_004'=>15];
                    $links = [];
                    foreach ($weights as $suffix => $points) {
                        $outcome = \App\Models\LearningOutcome::where('code','IT_VF_LU_'.$suffix)->firstOrFail();
                        $links[$outcome->id] = ['max_points'=>$points];
                    }
                    $project->learningOutcomes()->sync($links);
                }
            }
            $removed = $student->assignments()->whereNotIn('onderwerp',$keep)->get();
            // Deze seeder is alleen voor de lokale voorbeeldstudent; bewaar projecten met uploads.
            foreach ($removed as $project) {
                if ($project->files()->exists()) continue;
                DB::table('activity_xp')->where('user_id',$student->id)->where('source','project')->where('source_id',$project->id)->delete();
                $project->learningOutcomes()->detach();
                $project->delete();
            }
            // In deze beginsituatie is geen leeruitkomst al volledig behaald.
            DB::table('activity_xp')->where('user_id',$student->id)->where('source','outcome')->delete();
        });
    }
}
