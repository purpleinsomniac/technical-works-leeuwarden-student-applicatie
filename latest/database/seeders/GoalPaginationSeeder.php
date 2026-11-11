<?php

namespace Database\Seeders;

use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\User;
use Illuminate\Database\Seeder;

class GoalPaginationSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('email', 'student@example.test')->first();
        if (!$student) return;
        $examples = [
            ['001', 'Een werkplek stap voor stap installeren', 'Ik wil onder begeleiding een werkplek installeren en alle controles vastleggen in een checklist.'],
            ['002', 'Technische problemen herkennen', 'Ik wil bij een storing de symptomen verzamelen en het probleem duidelijk aan mijn begeleider uitleggen.'],
            ['004', 'Duidelijk samenwerken binnen mijn team', 'Ik wil tijdens een teamopdracht mijn taken afstemmen en iedere werkdag mijn voortgang bespreken.'],
            ['005', 'Mijn planning en voortgang bijhouden', 'Ik wil voor mijn werkzaamheden een haalbare planning maken en dagelijks bijhouden wat is afgerond.'],
            ['007', 'Veilig en zorgvuldig werken', 'Ik wil bij iedere installatie de veiligheidsafspraken volgen en mijn werk controleren met mijn begeleider.'],
        ];
        foreach ($examples as [$number, $title, $description]) {
            $outcome = LearningOutcome::where('code', 'IT_OF_LU_Hardskills_'.$number)->firstOrFail();
            LearningGoal::firstOrCreate(
                ['gebruiker_id'=>$student->id, 'onderwerp'=>$title],
                ['leeruitkomst_id'=>$outcome->id, 'omschrijving'=>$description, 'status'=>'actief'],
            );
        }
    }
}
