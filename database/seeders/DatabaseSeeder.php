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

        // Inhoudelijke titels voor leeruitkomsten die in het bronbestand nog een generieke nummernaam hebben.
        $leeruitkomstTitels = [
            'IT_OF_LU_Hardskills_003' => 'Basisontwerpen toepassen',
            'IT_OF_LU_Hardskills_004' => 'Technisch samenwerken en communiceren',
            'IT_OF_LU_Hardskills_005' => 'Voortgang bijhouden en rapporteren',
            'IT_OF_LU_Hardskills_006' => 'Kwaliteit verbeteren met tools',
            'IT_OF_LU_Hardskills_007' => 'Veilig en volgens normen werken',
            'IT_OF_LU_Hardskills_008' => 'Keuzes toelichten en onderbouwen',
            'IT_OF_LU_Softskills_001' => 'Kritisch en analytisch denken',
            'IT_OF_LU_Softskills_002' => 'Creatieve oplossingen bedenken',
            'IT_OF_LU_Softskills_004' => 'Duidelijk communiceren en feedback geven',
            'IT_OF_LU_Softskills_005' => 'Analytisch keuzes maken',
            'IT_OF_LU_Softskills_006' => 'Flexibel omgaan met uitdagingen',
            'IT_OF_LU_Softskills_007' => 'Ethisch en privacybewust werken',
            'IT_VF_LU_Hardskills_001' => 'Complexe taken uitvoeren',
            'IT_VF_LU_Hardskills_002' => 'Problemen analyseren',
            'IT_VF_LU_Hardskills_003' => 'Technische oplossingen uitwerken',
            'IT_VF_LU_Hardskills_004' => 'Effectief samenwerken en communiceren',
            'IT_VF_LU_Hardskills_006' => 'Data analyseren en verbeteren',
            'IT_VF_LU_Hardskills_007' => 'Moderne tools effectief inzetten',
            'IT_VF_LU_Hardskills_008' => 'Werk testen en verbeteren',
            'IT_VF_LU_Softskills_001' => 'Keuzes onderbouwen en bijsturen',
            'IT_VF_LU_Softskills_002' => 'Technologische vraagstukken analyseren',
            'IT_VF_LU_Softskills_003' => 'Innovatieve oplossingen ontwikkelen',
            'IT_VF_LU_Softskills_005' => 'Samenwerken in multidisciplinaire teams',
            'IT_VF_LU_Softskills_006' => 'Veerkrachtig en flexibel handelen',
            'IT_VF_LU_Softskills_007' => 'Proactief blijven leren',
            'IT_VF_LU_Softskills_008' => 'Reflecteren en doorzetten',
            'IT_AF_LU_Hardskills_001' => 'Werk plannen en organiseren',
            'IT_AF_LU_Hardskills_002' => 'Voortgang bewaken en bijsturen',
            'IT_AF_LU_Hardskills_003' => 'Technische oplossingen ontwerpen',
            'IT_AF_LU_Hardskills_005' => 'Uitgebreid testplan opstellen',
            'IT_AF_LU_Hardskills_006' => 'Complexe tests uitvoeren',
            'IT_AF_LU_Hardskills_007' => 'Testresultaten rapporteren',
            'IT_AF_LU_Hardskills_008' => 'Verbeteringen analyseren en doorvoeren',
            'IT_AF_LU_Softskills_001' => 'Problemen herkennen en escaleren',
            'IT_AF_LU_Softskills_002' => 'Actief deelnemen aan projectoverleg',
            'IT_AF_LU_Softskills_003' => 'Professioneel feedback geven en ontvangen',
            'IT_AF_LU_Softskills_004' => 'Reflecteren op prestaties en samenwerking',
            'IT_AF_LU_Softskills_005' => 'Gebruikers instrueren over risico’s',
            'IT_AF_LU_Softskills_006' => 'Systemen bruikbaar documenteren',
            'IT_AF_LU_Softskills_007' => 'Gebruikers begeleiden en instructies verbeteren',
            'IT_AF_LU_Softskills_008' => 'Systeemonderdelen in kaart brengen',
        ];

        foreach ($leeruitkomsten as $leeruitkomst) {
            LearningOutcome::updateOrCreate( 
                ['code' => $leeruitkomst['code']],
                [
                    'studiefase_id' => $studiefases[$leeruitkomst['phase']]->id,
                    'titel' => $leeruitkomstTitels[$leeruitkomst['code']] ?? $leeruitkomst['short'],
                    'type' => $leeruitkomst['kind'],
                    'omschrijving' => $leeruitkomst['description'],
                    'normwaarden' => (string) $leeruitkomst['target'],
                ],
            );
        }

        $demoUsers = [
            'student' => ['Sam de Vries', 'student@example.test'],
            'coach' => ['Mevrouw Jansen', 'coach@example.test'],
            'teamleider' => ['Onderwijsmanager', 'manager@example.test'],
            'beheerder' => ['Applicatiebeheerder', 'admin@example.test'],
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
                ['gevalideerde_uren' => $uren, 'normwaarden' => (string) $gegevens['target'], 'status' => $status],
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
            [
                'onderwerp' => 'Probleem analyseren en advies geven',
                'type' => 'Onderzoeksopdracht',
                'omschrijving' => 'Onderzoek een terugkerend ICT-probleem, onderbouw de oorzaak en presenteer een haalbaar verbeteradvies.',
                'status' => 'active',
                'inleverdatum' => '2026-12-08',
                'gevalideerde_uren' => 12,
                'codes' => ['IT_OF_LU_Softskills_005', 'IT_VF_LU_Hardskills_003', 'IT_VF_LU_Softskills_002'],
            ],
            [
                'onderwerp' => 'Securityscan van een werkplek',
                'type' => 'Securityopdracht',
                'omschrijving' => 'Voer een securityscan uit, leg kwetsbaarheden vast en beschrijf welke maatregelen direct nodig zijn.',
                'status' => 'active',
                'inleverdatum' => '2026-10-12',
                'gevalideerde_uren' => 8,
                'codes' => ['IT_OF_LU_Hardskills_006', 'IT_VF_LU_Hardskills_002'],
            ],
            [
                'onderwerp' => 'Prototype presenteren aan opdrachtgever',
                'type' => 'Presentatieopdracht',
                'omschrijving' => 'Presenteer het prototype, verzamel gerichte feedback en vertaal die naar concrete vervolgstappen.',
                'status' => 'review',
                'inleverdatum' => '2026-10-25',
                'gevalideerde_uren' => 24,
                'codes' => ['IT_VF_LU_Softskills_001', 'IT_AF_LU_Softskills_003'],
            ],
            [
                'onderwerp' => 'Persoonlijk ontwikkelportfolio',
                'type' => 'Reflectieopdracht',
                'omschrijving' => 'Bundel bewijsstukken, feedback en reflecties tot een overzichtelijk portfolio van je ontwikkeling.',
                'status' => 'completed',
                'inleverdatum' => '2026-09-30',
                'gevalideerde_uren' => 32,
                'codes' => ['IT_OF_LU_Softskills_004', 'IT_VF_LU_Softskills_004', 'IT_AF_LU_Softskills_004'],
            ],
            [
                'onderwerp' => 'Dashboard voor servicedeskdata',
                'type' => 'Data- en bouwopdracht',
                'omschrijving' => 'Ontwerp een dashboard dat servicedeskdata begrijpelijk maakt en test het met toekomstige gebruikers.',
                'status' => 'active',
                'inleverdatum' => '2027-02-12',
                'gevalideerde_uren' => 0,
                'codes' => ['IT_VF_LU_Hardskills_004', 'IT_AF_LU_Hardskills_004', 'IT_AF_LU_Softskills_001'],
            ],
            [
                'onderwerp' => 'Incidentrapport en verbetervoorstel',
                'type' => 'Analyseopdracht',
                'omschrijving' => 'Analyseer een afgehandeld incident en werk de belangrijkste verbeterpunten uit in een helder voorstel.',
                'status' => 'review',
                'inleverdatum' => '2026-10-27',
                'gevalideerde_uren' => 18,
                'codes' => ['IT_OF_LU_Hardskills_002', 'IT_VF_LU_Softskills_005'],
            ],
            [
                'onderwerp' => 'Gebruikerstest uitvoeren',
                'type' => 'Testopdracht',
                'omschrijving' => 'Bereid een gebruikerstest voor, observeer de deelnemers en verwerk de resultaten in verbeteracties.',
                'status' => 'review',
                'inleverdatum' => '2026-10-29',
                'gevalideerde_uren' => 20,
                'codes' => ['IT_VF_LU_Hardskills_006', 'IT_AF_LU_Softskills_002'],
            ],
            [
                'onderwerp' => 'Handleiding voor nieuwe medewerkers',
                'type' => 'Documentatieopdracht',
                'omschrijving' => 'Schrijf en test een praktische handleiding waarmee nieuwe medewerkers zelfstandig kunnen starten.',
                'status' => 'completed',
                'inleverdatum' => '2026-08-21',
                'gevalideerde_uren' => 28,
                'codes' => ['IT_OF_LU_Softskills_006', 'IT_VF_LU_Hardskills_007'],
            ],
            [
                'onderwerp' => 'Netwerkprototype opleveren',
                'type' => 'Ontwerp- en bouwopdracht',
                'omschrijving' => 'Ontwerp, bouw en demonstreer een werkend netwerkprototype volgens de afgesproken eisen.',
                'status' => 'completed',
                'inleverdatum' => '2026-07-14',
                'gevalideerde_uren' => 54,
                'codes' => ['IT_VF_LU_Hardskills_001', 'IT_AF_LU_Hardskills_002'],
            ],
            [
                'onderwerp' => 'Back-up- en herstelplan',
                'type' => 'Beheeropdracht',
                'omschrijving' => 'Maak een back-up- en herstelplan, test de herstelprocedure en documenteer de resultaten.',
                'status' => 'active',
                'inleverdatum' => '2026-10-05',
                'gevalideerde_uren' => 6,
                'codes' => ['IT_OF_LU_Hardskills_007', 'IT_VF_LU_Hardskills_008'],
            ],
            [
                'onderwerp' => 'Reflectie op klantgesprek',
                'type' => 'Reflectieopdracht',
                'omschrijving' => 'Werk een klantgesprek uit, reflecteer op je communicatie en beschrijf een concrete volgende stap.',
                'status' => 'active',
                'inleverdatum' => '2026-10-20',
                'gevalideerde_uren' => 4,
                'codes' => ['IT_OF_LU_Softskills_001', 'IT_VF_LU_Softskills_003'],
            ],
        ];

        foreach ($opdrachten as $gegevens) {
            // In de demo werkt de student aan één huidig project.
            $gegevens['status'] = $gegevens['onderwerp'] === 'Werkplek installeren' ? 'active' : 'completed';
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

        $this->call(SchoolPointsSeeder::class);
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
        $voorbeeldLeerdoelen = [
            [
                'code' => 'IT_VF_LU_Hardskills_001',
                'onderwerp' => 'Complexe taken uitvoeren',
                'omschrijving' => 'Ik wil complexe technische taken zelfstandig plannen, uitvoeren en mijn gemaakte keuzes duidelijk onderbouwen.',
                'status' => 'actief',
            ],
            [
                'code' => 'IT_AF_LU_Hardskills_005',
                'onderwerp' => 'Voortgang bewaken en bijsturen',
                'omschrijving' => 'Ik wil de voortgang van mijn werk tijdig controleren en mijn aanpak aanpassen wanneer dat nodig is.',
                'status' => 'afgerond',
            ],
            [
                'code' => 'IT_OF_LU_Hardskills_008',
                'onderwerp' => 'Technische keuzes zelfstandig uitleggen',
                'omschrijving' => 'Ik wil na een installatie duidelijk kunnen toelichten welke keuzes ik heb gemaakt en waarom.',
                'status' => 'afgerond',
            ],
        ];

        foreach ($voorbeeldLeerdoelen as $voorbeeldLeerdoel) {
            $leeruitkomst = LearningOutcome::where('code', $voorbeeldLeerdoel['code'])->firstOrFail();
            LearningGoal::updateOrCreate(
                ['gebruiker_id' => $student->id, 'onderwerp' => $voorbeeldLeerdoel['onderwerp']],
                [
                    'leeruitkomst_id' => $leeruitkomst->id,
                    'omschrijving' => $voorbeeldLeerdoel['omschrijving'],
                    'status' => $voorbeeldLeerdoel['status'],
                ],
            );
        }
    }
}
