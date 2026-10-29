<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\Progress;
use App\Models\Reflection;
use App\Models\Role;
use App\Models\StudyPhase;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect(['student', 'coach', 'teamleider', 'beheerder'])
            ->mapWithKeys(function (string $naam): array {
                $rol = Role::updateOrCreate(['naam' => $naam]);

                return [$naam => $rol];
            });

        $studiefases = collect([
            'OF' => ['Oriëntatiefase', 'De eerste fase van de opleiding.'],
            'VF' => ['Verdiepingsfase', 'De fase waarin kennis en vaardigheden worden verdiept.'],
            'AF' => ['Afrondingsfase', 'De afsluitende fase van de opleiding.'],
        ])->mapWithKeys(function (array $gegevens, string $code): array {
            $studiefase = StudyPhase::updateOrCreate(
                ['naam' => $gegevens[0]],
                ['omschrijving' => $gegevens[1]],
            );

            return [$code => $studiefase];
        });

        $leeruitkomsten = json_decode(
            file_get_contents(database_path('outcomes.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($leeruitkomsten as $leeruitkomst) {
            LearningOutcome::updateOrCreate( 
                ['code' => $leeruitkomst['code']],
                [
                    'studiefase_id' => $studiefases[$leeruitkomst['phase']]->id,
                    'titel' => $leeruitkomst['short'],
                    'type' => $leeruitkomst['kind'],
                    'omschrijving' => $leeruitkomst['description'],
                    'normuren' => $leeruitkomst['target'],
                ],
            );
        }

        $demoUsers = [
            'student' => ['Sam de Vries', 'student@example.test'],
            'coach' => ['Mevrouw Jansen', 'coach@example.test'],
            'teamleider' => ['Onderwijsmanager', 'manager@example.test'],
            'beheerder' => ['Applicatiebeheerder', 'admin@ITIG.edu'],
        ];

        foreach ($demoUsers as $rol => [$naam, $email]) {
            $gebruiker = User::updateOrCreate(
                ['email' => $email],
                [
                    'naam' => $naam,
                    'rol' => $rol,
                    'hoofdrol_id' => $roles[$rol]->id,
                    'studiefase_id' => $rol === 'student' ? $studiefases['OF']->id : null,
                    'password' => 'Demo12345!',
                ],
            );

            // Alleen de beheerder krijgt extra rollen om alle schermen te testen.
            $gebruiker->extraRoles()->sync(
                $rol === 'beheerder' ? $roles->pluck('id')->all() : [],
            );
        }

        $student = User::where('email', 'student@example.test')->firstOrFail();
        $coach = User::where('email', 'coach@example.test')->firstOrFail();

        // Elke voortgangsregel is gekoppeld aan precies één student en leeruitkomst.
        foreach ($leeruitkomsten as $gegevens) {
            $leeruitkomst = LearningOutcome::where('code', $gegevens['code'])->firstOrFail();
            $uren = $gegevens['hours'] ?? 0;
            $percentage = $gegevens['pct'] ?? 0;
            $status = match (true) {
                $percentage >= 80 => 'behaald',
                $percentage >= 40 => 'in_ontwikkeling',
                $percentage > 0 => 'gestart',
                default => 'niet_gestart',
            };

            Progress::updateOrCreate(
                ['gebruiker_id' => $student->id, 'leeruitkomst_id' => $leeruitkomst->id],
                ['gevalideerde_uren' => $uren, 'normuren' => $gegevens['target'], 'status' => $status],
            );
        }

        $opdrachten = [
            [
                'onderwerp' => 'Werkplek installeren',
                'type' => 'Installatieopdracht',
                'omschrijving' => 'Installeer en configureer een veilige werkplek en leg de gekozen werkwijze vast.',
                'status' => 'active',
                'inleverdatum' => '2026-11-30',
                'gevalideerde_uren' => 42,
                'codes' => ['IT_OF_LU_Hardskills_001', 'IT_OF_LU_Hardskills_007', 'IT_OF_LU_Hardskills_005'],
            ],
            [
                'onderwerp' => 'Servicedesk als team',
                'type' => 'Samenwerkingsopdracht',
                'omschrijving' => 'Los als team meldingen op, communiceer met gebruikers en reflecteer op de samenwerking.',
                'status' => 'review',
                'inleverdatum' => '2026-10-18',
                'gevalideerde_uren' => 36,
                'codes' => ['IT_OF_LU_Hardskills_002', 'IT_OF_LU_Hardskills_004', 'IT_OF_LU_Softskills_003'],
            ],
            [
                'onderwerp' => 'Netwerkdocumentatie',
                'type' => 'Documentatieopdracht',
                'omschrijving' => 'Breng een bestaand netwerk in kaart en schrijf bruikbare documentatie voor een collega.',
                'status' => 'completed',
                'inleverdatum' => '2026-06-28',
                'gevalideerde_uren' => 48,
                'codes' => ['IT_VF_LU_Hardskills_005', 'IT_VF_LU_Hardskills_007', 'IT_VF_LU_Softskills_004'],
            ],
            [
                'onderwerp' => 'Softwaremodule bouwen en testen',
                'type' => 'Bouw- en testopdracht',
                'omschrijving' => 'Bouw zelfstandig een softwaremodule en toon met een testplan aan dat deze aan de eisen voldoet.',
                'status' => 'active',
                'inleverdatum' => '2027-01-15',
                'gevalideerde_uren' => 0,
                'codes' => ['IT_AF_LU_Hardskills_004', 'IT_AF_LU_Hardskills_005', 'IT_AF_LU_Hardskills_006'],
            ],
        ];

        foreach ($opdrachten as $gegevens) {
            $codes = $gegevens['codes'];
            unset($gegevens['codes']);
            $opdracht = Assignment::updateOrCreate(
                ['gebruiker_id' => $student->id, 'onderwerp' => $gegevens['onderwerp']],
                $gegevens,
            );
            $opdracht->learningOutcomes()->sync(
                LearningOutcome::whereIn('code', $codes)->pluck('id')->all(),
            );
        }

        $eersteLeeruitkomst = LearningOutcome::where('code', 'IT_OF_LU_Hardskills_001')->firstOrFail();
        Reflection::updateOrCreate(
            ['gebruiker_id' => $student->id, 'onderwerp' => 'Installatieopdracht'],
            [
                'leeruitkomst_id' => $eersteLeeruitkomst->id,
                'situatie' => 'Ik heb voor de installatieopdracht een nieuwe werkplek ingericht.',
                'wat_ging_goed' => 'De installatie verliep volgens mijn stappenplan en de controles waren in orde.',
                'wat_kan_beter' => 'Ik wil mijn technische keuzes vooraf duidelijker documenteren.',
                'volgende_stap' => 'Bij de volgende opdracht voeg ik vóór de uitvoering een onderbouwd keuzeoverzicht toe.',
            ],
        );
        Feedback::updateOrCreate(
            ['student_id' => $student->id, 'onderwerp' => 'Presentatie installatieplan'],
            [
                'auteur_id' => $coach->id,
                'leeruitkomst_id' => $eersteLeeruitkomst->id,
                'type' => 'received',
                'persoon' => $coach->naam.' · coach',
                'wat_ging_goed' => 'Je legde de belangrijkste technische keuzes rustig en in een duidelijke volgorde uit.',
                'wat_kan_beter' => 'Onderbouw bij iedere keuze ook welk risico je ermee voorkomt.',
                'volgende_stap' => 'Voeg bij de volgende presentatie per keuze een risico en een alternatief toe.',
            ],
        );
        LearningGoal::updateOrCreate(
            ['gebruiker_id' => $student->id, 'status' => 'actief'],
            [
                'leeruitkomst_id' => $eersteLeeruitkomst->id,
                'onderwerp' => 'Technische keuzes zelfstandig uitleggen',
                'omschrijving' => 'Ik wil na een installatie duidelijk kunnen toelichten welke keuzes ik heb gemaakt en waarom.',
            ],
        );
    }
}
