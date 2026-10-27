<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demoUsers = [
            'student' => ['Sam de Vries', 'student@example.test'],
            'coach' => ['Mevrouw Jansen', 'coach@example.test'],
            'manager' => ['Onderwijsmanager', 'manager@example.test'],
            'admin' => ['Applicatiebeheerder', 'admin@example.test'],
        ];

        foreach ($demoUsers as $role => [$name, $email]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => $role,
                    'password' => 'Demo12345!',
                ],
            );
        }
    }
}
