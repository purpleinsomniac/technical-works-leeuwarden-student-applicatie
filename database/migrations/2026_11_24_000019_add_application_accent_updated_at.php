<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicatie_instellingen', function (Blueprint $table): void {
            // Milliseconden maken globale en persoonlijke versies direct vergelijkbaar.
            $table->unsignedBigInteger('application_accent_updated_at')->default(0);
        });

        DB::table('applicatie_instellingen')->update([
            'application_accent_updated_at' => now()->valueOf(),
        ]);
    }

    public function down(): void
    {
        Schema::table('applicatie_instellingen', function (Blueprint $table): void {
            $table->dropColumn('application_accent_updated_at');
        });
    }
};
