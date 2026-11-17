<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gebruikers', function (Blueprint $table): void {
            $table->enum('rol', ['student', 'coach', 'teamleider', 'beheerder', 'admin'])
                ->default('student')
                ->change();
        });
    }

    public function down(): void
    {
        if (DB::table('gebruikers')->where('rol', 'admin')->exists()) {
            throw new RuntimeException('Cannot remove the admin role while admin accounts still exist.');
        }

        Schema::table('gebruikers', function (Blueprint $table): void {
            $table->enum('rol', ['student', 'coach', 'teamleider', 'beheerder'])
                ->default('student')
                ->change();
        });
    }
};
