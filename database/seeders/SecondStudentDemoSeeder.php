<?php

namespace Database\Seeders;

use App\Models\{Assignment, Feedback, LearningGoal, LearningOutcome, Progress, Reflection, Role, StudyPhase, User};
use App\Services\{ChallengeService, ProjectPoints};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecondStudentDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $phase = StudyPhase::where('naam','Oriëntatiefase')->firstOrFail();
            $role = Role::where('naam','student')->firstOrFail();
            $student = User::firstOrCreate(['email'=>'student2@example.test'], [
                'naam'=>'Wesley Rinsma', 'password'=>'Demo12345!', 'rol'=>'student',
                'hoofdrol_id'=>$role->id, 'studiefase_id'=>$phase->id, 'leerjaar'=>1,
            ]);
            if ($student->rol !== 'student') throw new \RuntimeException('student2@example.test bestaat al met een andere rol.');
            if (!$student->studentnummer) $student->forceFill(['studentnummer'=>'STU'.str_pad((string)$student->id,6,'0',STR_PAD_LEFT)])->save();
            $projects = [];
            $examples = [
                ['Een laptop gebruiksklaar maken','Installatieopdracht','Installeer een besturingssysteem, maak een gebruikersaccount aan en controleer de beveiliging.',60,35,['Hardskills_001'=>20,'Hardskills_003'=>20,'Softskills_001'=>10,'Softskills_004'=>10]],
                ['Een printerstoring onderzoeken','Onderzoeksopdracht','Onderzoek waarom een netwerkprinter niet bereikbaar is en leg je oplossing stap voor stap vast.',40,18,['Hardskills_002'=>15,'Hardskills_005'=>15,'Softskills_002'=>10]],
                ['Een eenvoudige website opzetten','Softwareopdracht','Bouw een persoonlijke website en houd bij welke stappen je zelfstandig kunt uitvoeren.',0,0,['Hardskills_004'=>20,'Hardskills_006'=>20,'Softskills_003'=>20]],
            ];
            foreach ($examples as [$title,$type,$description,$points,$days,$weights]) {
                $links = [];
                foreach ($weights as $suffix=>$maximum) {
                    $outcome = LearningOutcome::where('code','IT_OF_LU_'.$suffix)->firstOrFail();
                    $links[$outcome->id] = ['max_points'=>$maximum,'urenpercentage'=>round(100*$maximum/array_sum($weights))];
                }
                $project = $student->assignments()->firstOrCreate(['onderwerp'=>$title],[
                    'type'=>$type,'omschrijving'=>$description,'status'=>$points ? 'completed' : 'active',
                    'gevalideerde_uren'=>$points,'inleverdatum'=>$days ? today()->subDays($days-2) : today()->addDays(12),
                ]);
                if ($project->wasRecentlyCreated) {
                    $project->learningOutcomes()->sync($links);
                    $project->forceFill(['created_at'=>now()->subDays($days+7),'updated_at'=>now()->subDays($days),'completed_at'=>$points ? now()->subDays($days) : null])->saveQuietly();
                }
                $projects[] = $project;
            }
            $goalExamples = [
                ['Mijn technische aanpak duidelijk uitleggen','Ik wil bij elke oplossing in eigen woorden uitleggen waarom ik voor deze aanpak kies.','Softskills_004','actief'],
                ['Zelfstandig een webpagina bouwen','Ik wil mijn eerste webpagina zonder een stappenplan opbouwen en mijn keuzes vastleggen.','Hardskills_004','actief'],
                ['Een installatie zorgvuldig controleren','Ik wil na het installeren een checklist gebruiken en aantonen dat alle controles zijn uitgevoerd.','Hardskills_001','afgerond'],
            ];
            foreach ($goalExamples as [$title,$description,$suffix,$status]) {
                $student->learningGoals()->firstOrCreate(['onderwerp'=>$title],[
                    'leeruitkomst_id'=>LearningOutcome::where('code','IT_OF_LU_'.$suffix)->firstOrFail()->id,
                    'omschrijving'=>$description,'status'=>$status,
                ]);
            }
            $outcome = LearningOutcome::where('code','IT_OF_LU_Hardskills_002')->firstOrFail();
            Reflection::firstOrCreate(['gebruiker_id'=>$student->id,'onderwerp'=>'Terugblik op mijn printeronderzoek'],[
                'opdracht_id'=>$projects[1]->id,'leeruitkomst_id'=>$outcome->id,
                'situatie'=>'De printer was niet bereikbaar. Ik heb eerst de verbinding en daarna de instellingen gecontroleerd.',
                'wat_ging_goed'=>'Door mijn controles op te schrijven kon ik gericht zoeken en vond ik een verkeerd ingesteld IP-adres.',
                'wat_kan_beter'=>'Ik vroeg te snel om hulp voordat ik de netwerkinstellingen zelf had bekeken.',
                'volgende_stap'=>'Bij een volgende storing probeer ik eerst drie mogelijke oorzaken en noteer ik de resultaten.',
            ]);
            $coach = User::where('rol','coach')->first();
            if ($coach) Feedback::firstOrCreate(['student_id'=>$student->id,'onderwerp'=>'Systematisch onderzoeken'],[
                'auteur_id'=>$coach->id,'opdracht_id'=>$projects[1]->id,'leeruitkomst_id'=>$outcome->id,
                'type'=>'received','persoon'=>$coach->naam.' · coach',
                'wat_ging_goed'=>'Je controleerde de mogelijke oorzaken rustig en legde je oplossing duidelijk uit.',
                'wat_kan_beter'=>'Beschrijf ook waarom je een oorzaak uitsluit, zodat je onderzoek beter te volgen is.',
                'volgende_stap'=>'Maak bij je volgende project een kort overzicht van oorzaak, controle en conclusie.',
            ]);
            $service = app(ProjectPoints::class);
            foreach ($service->forStudent($student) as $id=>$points) {
                $target = $service->norm(LearningOutcome::with('studyPhase')->findOrFail($id));
                Progress::firstOrCreate(['gebruiker_id'=>$student->id,'leeruitkomst_id'=>$id],[
                    'gevalideerde_uren'=>$points,'normwaarden'=>(string)$target,'status'=>$points >= $target ? 'behaald' : 'in_ontwikkeling',
                ]);
            }
            $state = $student->appState()->firstOrNew();
            $data = $state->gegevens ?? [];
            if (!array_key_exists('weekly-plan',$data)) {
                $data['weekly-plan'] = [
                    ['id'=>'wesley-plan-1','week'=>today()->startOfWeek()->format('Y-m-d'),'text'=>'De startpagina van mijn website bouwen','done'=>false],
                    ['id'=>'wesley-plan-2','week'=>today()->startOfWeek()->format('Y-m-d'),'text'=>'Mijn printeronderzoek met de coach bespreken','done'=>true],
                ];
                $state->gegevens = $data;
                $state->save();
            }
            app(ChallengeService::class)->sync($student);
        });
    }
}
