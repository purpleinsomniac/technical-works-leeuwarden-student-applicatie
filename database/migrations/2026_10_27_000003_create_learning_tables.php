<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leeruitkomsten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studiefase_id')->constrained('studiefases')->cascadeOnDelete();
            $table->string('code', 100)->unique();
            $table->string('titel');
            $table->string('type', 45)->nullable();
            $table->text('omschrijving');
            $table->unsignedInteger('normuren')->nullable();
            $table->timestamps();
        });

        Schema::create('leerdoelen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gebruiker_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->foreignId('leeruitkomst_id')->nullable()->constrained('leeruitkomsten')->nullOnDelete();
            $table->string('onderwerp');
            $table->text('omschrijving');
            $table->string('status', 30)->default('actief');
            $table->timestamps();
        });

        Schema::create('opdrachten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gebruiker_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->string('onderwerp');
            $table->text('omschrijving')->nullable();
            $table->unsignedInteger('gevalideerde_uren')->default(0);
            $table->date('inleverdatum')->nullable();
            $table->string('status', 30)->default('concept');
            $table->timestamps();
        });

        Schema::create('opdracht_leeruitkomst', function (Blueprint $table) {
            $table->foreignId('opdracht_id')->constrained('opdrachten')->cascadeOnDelete();
            $table->foreignId('leeruitkomst_id')->constrained('leeruitkomsten')->cascadeOnDelete();
            $table->unsignedTinyInteger('urenpercentage')->default(100);
            $table->primary(['opdracht_id', 'leeruitkomst_id']);
        });

        Schema::create('reflecties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gebruiker_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->foreignId('opdracht_id')->nullable()->constrained('opdrachten')->nullOnDelete();
            $table->foreignId('leeruitkomst_id')->constrained('leeruitkomsten')->cascadeOnDelete();
            $table->string('onderwerp');
            $table->text('situatie');
            $table->text('wat_ging_goed');
            $table->text('wat_kan_beter');
            $table->text('volgende_stap');
            $table->timestamps();
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auteur_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->foreignId('opdracht_id')->nullable()->constrained('opdrachten')->nullOnDelete();
            $table->foreignId('leeruitkomst_id')->constrained('leeruitkomsten')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('onderwerp');
            $table->text('wat_ging_goed');
            $table->text('wat_kan_beter');
            $table->text('volgende_stap');
            $table->timestamps();
        });

        Schema::create('voortgang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gebruiker_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->foreignId('leeruitkomst_id')->constrained('leeruitkomsten')->cascadeOnDelete();
            $table->unsignedInteger('gevalideerde_uren')->default(0);
            $table->unsignedInteger('normuren')->nullable();
            $table->string('status', 30)->default('niet_gestart');
            $table->timestamps();
            $table->unique(['gebruiker_id', 'leeruitkomst_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voortgang');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('reflecties');
        Schema::dropIfExists('opdracht_leeruitkomst');
        Schema::dropIfExists('opdrachten');
        Schema::dropIfExists('leerdoelen');
        Schema::dropIfExists('leeruitkomsten');
    }
};
