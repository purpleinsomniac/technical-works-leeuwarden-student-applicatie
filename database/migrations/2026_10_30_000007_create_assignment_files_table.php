<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opdracht_bestanden', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('opdracht_id')->constrained('opdrachten')->cascadeOnDelete();
            $table->string('originele_naam');
            $table->string('opslagpad');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('bestandsgrootte');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opdracht_bestanden');
    }
};
