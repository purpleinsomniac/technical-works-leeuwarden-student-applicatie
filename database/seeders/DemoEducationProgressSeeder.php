<?php

namespace Database\Seeders;

use App\Models\LearningOutcome;
use App\Models\StudyPhase;
use App\Models\User;
use App\Services\ChallengeService;
use App\Services\LearningProgress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoEducationProgressSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new \RuntimeException('De voorbeeldhistorie is alleen voor de lokale demo.');
        }

        DB::transaction(function (): void {
            $catalog = LearningOutcome::with('studyPhase')->orderBy('code')->get();
            $coach = User::where('email', 'coach@example.test')->where('rol', 'coach')->first();
            $profiles = [
                ['student@example.test', 'Sam de Vries', 3, 'Afrondingsfase', 2560, [
                    ['active', 'Toegankelijk klantportaal opleveren', 'Afrondingsfase', 'Maak het portaal toegankelijk en onderbouw de keuzes met gebruikerstests.'],
                ]],
                ['student2@example.test', 'Wesley Rinsma', 2, 'Verdiepingsfase', 1800, [
                    ['completed', 'Een reserveringsmodule bouwen', 'Verdiepingsfase', 'Bouw formulieren met servervalidatie en schrijf tests voor dubbele boekingen.'],
                    ['completed', 'Een API voor voorraadbeheer testen', 'Verdiepingsfase', 'Test foutmeldingen en ontbrekende gegevens met een aparte testdataset.'],
                    ['active', 'Een dashboardfilter verbeteren', 'Verdiepingsfase', 'Maak de filters toegankelijk en meet of de resultaten begrijpelijk zijn.'],
                    ['concept', 'Een versie van de app voorbereiden', 'Verdiepingsfase', 'Maak een planning voor testen, documentatie en overdracht.'],
                ]],
            ];

            foreach ($profiles as [$email, $name, $year, $phase, $goal, $projects]) {
                $student = User::where('email', $email)->where('rol', 'student')->where('naam', $name)->firstOrFail();
                $student->update(['leerjaar' => $year, 'studiefase_id' => StudyPhase::where('naam', $phase)->firstOrFail()->id]);
                $this->setDemoCompletionDates($student, $year);
                foreach ($projects as $offset => [$status, $title, $projectPhase, $description]) {
                    $skills = $catalog->filter(fn ($item) => $item->studyPhase?->naam === $projectPhase)->values();
                    $skill = $skills[($offset + 2) % $skills->count()];
                    $completed = $status === 'completed';
                    $project = $student->assignments()->firstOrCreate(['onderwerp' => $title], [
                        'type' => 'Softwareproject',
                        'omschrijving' => $description,
                        'status' => $status,
                        'gevalideerde_uren' => $completed ? 40 : 0,
                        'inleverdatum' => today()->addDays($completed ? -20 + $offset : 14 + $offset),
                    ]);
                    if ($project->wasRecentlyCreated) {
                        $project->learningOutcomes()->attach([
                            $skill->id => ['max_points' => 20, 'urenpercentage' => 50],
                            $skills[($offset + 9) % $skills->count()]->id => ['max_points' => 20, 'urenpercentage' => 50],
                        ]);
                        if ($completed) {
                            $project->forceFill(['completed_at' => $project->inleverdatum->copy()->subDay()])->saveQuietly();
                        }
                    }
                    if ($completed && $coach) {
                        \App\Models\Feedback::firstOrCreate([
                            'student_id' => $student->id, 'opdracht_id' => $project->id,
                            'onderwerp' => 'Terugblik · '.$title,
                        ], [
                            'auteur_id' => $coach->id, 'leeruitkomst_id' => $skill->id,
                            'type' => 'received', 'persoon' => $coach->naam,
                            'wat_ging_goed' => 'Je hebt de belangrijkste gebruikersscenario’s uitgewerkt.',
                            'wat_kan_beter' => 'Maak expliciet hoe de applicatie omgaat met ongeldige invoer.',
                            'volgende_stap' => 'Voeg per foutscenario een test en een duidelijke foutmelding toe.',
                        ]);
                    }
                }

                $scores = app(LearningProgress::class)->snapshot($student, $catalog)['outcomes'];
                $remaining = max(0, $goal - array_sum(array_column($scores, 'earned')));
                // Geef alleen aanvullende demo-beoordelingen, in bestaande stappen van tien.
                foreach (['Oriëntatiefase', 'Verdiepingsfase', 'Afrondingsfase'] as $phaseName) {
                    foreach ($catalog->filter(fn ($outcome) => $outcome->studyPhase?->naam === $phaseName) as $outcome) {
                        $score = $scores[$outcome->id];
                        $add = intdiv(min($remaining, $score['target'] - $score['earned']), 10) * 10;
                        if ($add < 10) continue;
                        $student->progress()->updateOrCreate(['leeruitkomst_id' => $outcome->id], [
                            'gevalideerde_uren' => (int) $score['review']?->gevalideerde_uren + $add,
                            'status' => $score['earned'] + $add === $score['target'] ? 'behaald' : 'in_ontwikkeling',
                        ]);
                        $remaining -= $add;
                    }
                }
                if ($email === 'student@example.test') {
                    $this->addSamReflections($student);
                }
                app(ChallengeService::class)->sync($student);
            }
        });
    }

    private function addSamReflections(User $student): void
    {
        $examples = [
            ['Servicedesk als team', 'Een storing reproduceerbaar vastleggen', 'Ik noteerde per melding het apparaat, de foutmelding en de stappen om het probleem na te bootsen.', 'Ik wil bij terugkerende meldingen eerder zoeken naar een gemeenschappelijke oorzaak.'],
            ['Servicedesk als team', 'Een oplossing overdragen aan een collega', 'Mijn collega kon met mijn notities verder terwijl ik met een andere gebruiker sprak.', 'Ik sluit de overdracht voortaan af met een korte controle van de openstaande vragen.'],
            ['Probleem analyseren en advies geven', 'Alternatieven onderbouwen met meetgegevens', 'Ik vergeleek twee oplossingen met dezelfde test en verwerkte de meetgegevens in het advies.', 'Bij het volgende advies benoem ik ook de onzekerheden bij de gekozen meetmethode.'],
            ['Securityscan van een werkplek', 'Bevindingen prioriteren op risico', 'Ik scheidde de ernstige beveiligingsproblemen van meldingen met weinig gevolgen.', 'Ik wil bij elke bevinding beschrijven hoe we controleren dat het risico is opgelost.'],
            ['Securityscan van een werkplek', 'Een scanresultaat handmatig controleren', 'Ik controleerde een waarschuwing handmatig en voorkwam daarmee dat een onjuiste melding in het rapport kwam.', 'Ik houd voortaan een kort logboek van de handmatige controles en de uitkomsten bij.'],
            ['Persoonlijk ontwikkelportfolio', 'Bewijs koppelen aan mijn eigen bijdrage', 'Ik liet bij het bewijs zien welke keuze ik had gemaakt en hoe mijn werk is getest.', 'Ik wil bewijsmateriaal tijdens het project verzamelen in plaats van aan het einde.'],
            ['Netwerkdocumentatie', 'Een collega het netwerkdiagram laten testen', 'Een collega kon met het diagram de route en de rol van de onderdelen uitleggen.', 'Ik voeg bij de volgende versie ook de wijzigingen toe die nog moeten worden uitgevoerd.'],
            ['Prototype presenteren aan opdrachtgever', 'Feedback omzetten in concrete verbeteringen', 'Na de demonstratie vertaalde ik de feedback naar toetsbare verbeterpunten voor het prototype.', 'Ik laat de opdrachtgever bij de volgende demo een taak zelf uitvoeren en noteer waar die vastloopt.'],
        ];

        foreach ($examples as [$projectTitle, $title, $result, $next]) {
            $project = $student->assignments()->where('onderwerp', $projectTitle)->where('status', 'completed')
                ->with('learningOutcomes')->first();
            $skill = $project?->learningOutcomes->sortBy('id')->first();
            if (! $skill) continue;

            $reflection = $student->reflections()->withTrashed()->firstOrCreate(
                ['onderwerp' => $title, 'opdracht_id' => $project->id],
                [
                    'leeruitkomst_id' => $skill->id,
                    'situatie' => 'Na '.$projectTitle.' keek ik terug op mijn bijdrage en de uitkomst van het project.',
                    'wat_ging_goed' => $result,
                    'wat_kan_beter' => 'Ik kan mijn aanpak eerder bespreken en duidelijker vastleggen wat ik wil controleren.',
                    'volgende_stap' => $next,
                ],
            );
            if ($reflection->wasRecentlyCreated && $project->completed_at) {
                $date = $project->completed_at->copy()->addDays(2);
                $reflection->forceFill(['created_at' => $date, 'updated_at' => $date])->saveQuietly();
            }
        }
    }

    private function setDemoCompletionDates(User $student, int $year): void
    {
        // De bestaande demoprojecten zijn geïmporteerd op één dag, maar horen bij verschillende schooljaren.
        $history = $year === 3 ? [
            'Servicedesk als team' => [2, 3],
            'Probleem analyseren en advies geven' => [2, 5],
            'Securityscan van een werkplek' => [2, 7],
            'Persoonlijk ontwikkelportfolio' => [2, 9],
            'Netwerkdocumentatie' => [1, 4],
            'Prototype presenteren aan opdrachtgever' => [1, 8],
        ] : [
            'Een laptop gebruiksklaar maken' => [1, 3],
            'Een printerstoring onderzoeken' => [1, 7],
        ];

        $date = today();
        $schoolStart = $date->copy()->setDate($date->year - ($date->month < 9 ? 1 : 0), 9, 1);
        foreach ($history as $title => [$yearsAgo, $monthsAfterStart]) {
            $project = $student->assignments()->where('onderwerp', $title)->where('status', 'completed')->first();
            if (! $project || ($project->completed_at && $project->completed_at->lt($schoolStart))) {
                continue;
            }
            $project->forceFill([
                'completed_at' => $schoolStart->copy()->subYears($yearsAgo)->addMonths($monthsAfterStart),
            ])->saveQuietly();
        }
    }
}
