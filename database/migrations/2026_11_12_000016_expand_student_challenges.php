<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_challenges', function (Blueprint $table) {
            $table->unique(['user_id', 'challenge_id', 'phase']);
        });
        Schema::table('student_challenges', fn (Blueprint $table) => $table->dropUnique(['user_id', 'challenge_id']));
        Schema::table('opdrachten', fn (Blueprint $table) => $table->timestamp('completed_at')->nullable());
        DB::table('challenges')->insert([
            ['title'=>'Eerste stap','description'=>'Maak een persoonlijk leerdoel aan in deze fase.','type'=>'goals_created','target'=>1,'reward'=>25],
            ['title'=>'Bewust terugkijken','description'=>'Schrijf een reflectie die gekoppeld is aan een afgerond project in deze fase.','type'=>'project_reflections','target'=>1,'reward'=>50],
            ['title'=>'Van feedback naar actie','description'=>'Verwerk feedback in deze fase en laat je docent de verwerking bevestigen.','type'=>'feedback','target'=>1,'reward'=>75],
            ['title'=>'Bewijs geleverd','description'=>'Voeg een bewijsstuk toe aan een project in deze fase dat door de docent is afgerond. Goedkeuring geldt voor het project als geheel.','type'=>'evidence','target'=>1,'reward'=>50],
            ['title'=>'Op koers','description'=>'Rond twee projecten in deze fase uiterlijk op hun deadline af. Alleen geregistreerde afrondingsdatums tellen mee.','type'=>'on_time_projects','target'=>2,'reward'=>100],
            ['title'=>'Persoonlijke groei','description'=>'Rond een persoonlijk leerdoel af dat hoort bij deze fase.','type'=>'goals_completed','target'=>1,'reward'=>75],
            ['title'=>'Samen sterker','description'=>'Rond een samenwerkings- of teamproject in deze fase af en koppel er een reflectie aan over jouw bijdrage.','type'=>'team_reflections','target'=>1,'reward'=>100],
            ['title'=>'Veelzijdig ontwikkelen','description'=>'Behaal in deze fase projectpunten voor zowel soft skills als hard skills.','type'=>'skill_balance','target'=>2,'reward'=>75],
            ['title'=>'Stap voor stap','description'=>'Behaal 25% van het totale puntendoel van deze fase.','type'=>'phase_percentage','target'=>25,'reward'=>100],
            ['title'=>'Fase afgerond','description'=>'Behaal alle benodigde leeruitkomstpunten van deze fase.','type'=>'phase_percentage','target'=>100,'reward'=>200],
        ]);
    }

    public function down(): void
    {
        throw new RuntimeException('Deze migratie kan niet automatisch worden teruggedraaid zonder challengehistorie per fase te verliezen.');
    }
};
