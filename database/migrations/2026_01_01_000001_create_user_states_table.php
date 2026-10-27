<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gebruiker_statussen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gebruiker_id')->unique()->constrained('gebruikers')->cascadeOnDelete();
            $table->json('gegevens');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gebruiker_statussen');
    }
};
