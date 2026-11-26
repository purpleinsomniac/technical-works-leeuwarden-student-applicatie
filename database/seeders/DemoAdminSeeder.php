<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local') || ! config('demo.account_switcher')) {
            return;
        }

        $role = Role::firstOrCreate(['naam' => 'beheerder']);

        // Maak alleen een apart demoaccount aan. Laat bestaande accounts intact.
        User::firstOrCreate(
            ['email' => config('demo.accounts.beheerder.email')],
            [
                'naam' => 'Demo Applicatiebeheerder',
                'rol' => 'beheerder',
                'hoofdrol_id' => $role->id,
                'password' => Str::random(48),
            ],
        );
    }
}
