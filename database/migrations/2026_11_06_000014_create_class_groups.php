<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('naam');
            $table->string('schooljaar', 20)->nullable();
            $table->text('omschrijving')->nullable();
            $table->foreignId('coach_id')->nullable()->constrained('gebruikers')->nullOnDelete();
            $table->timestamps();
            $table->index(['coach_id', 'naam']);
        });

        Schema::create('class_group_student', function (Blueprint $table): void {
            $table->foreignId('class_group_id')->constrained('class_groups')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['class_group_id', 'student_id']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_group_student');
        Schema::dropIfExists('class_groups');
    }
};
