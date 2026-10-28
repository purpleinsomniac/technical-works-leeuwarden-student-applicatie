<?php

namespace Database\Seeders;

use App\Models\LearningOutcome;
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
    }
}
