<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectOverviewSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('email', 'student@example.test')->first();
        if (!$student) {
            return;
        }

        $student->assignments()->where('onderwerp', '!=', 'Werkplek installeren')->update(['status' => 'completed']);
        $student->assignments()->where('onderwerp', 'Werkplek installeren')->update(['status' => 'active']);
    }
}
