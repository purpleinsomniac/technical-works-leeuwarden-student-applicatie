<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('type');
            $table->unsignedInteger('target');
            $table->unsignedInteger('reward');
        });
        Schema::create('student_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained('challenges');
            $table->string('phase');
            $table->string('status')->default('active');
            $table->unsignedInteger('baseline')->default(0);
            $table->unsignedInteger('progress')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->unique(['user_id', 'challenge_id']);
        });
        Schema::create('xp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->foreignId('student_challenge_id')->unique()->constrained('student_challenges')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->timestamp('created_at');
        });
        DB::table('challenges')->insert([
            ['title'=>'Punten verzamelen','description'=>'Verdien 100 nieuwe punten in je huidige fase. Alleen punten sinds de start tellen mee.','type'=>'points','target'=>100,'reward'=>100],
            ['title'=>'Project afronden','description'=>'Rond een nieuw project in je huidige fase af. De docent moet het project goedkeuren.','type'=>'projects','target'=>1,'reward'=>150],
            ['title'=>'Terugkijken','description'=>'Schrijf een nieuwe reflectie bij een leeruitkomst in je huidige fase.','type'=>'reflections','target'=>1,'reward'=>50],
        ]);
    }
    public function down(): void
    {
        Schema::dropIfExists('xp_transactions');
        Schema::dropIfExists('student_challenges');
        Schema::dropIfExists('challenges');
    }
};
