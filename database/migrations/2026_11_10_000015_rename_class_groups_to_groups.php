<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('class_groups', 'groepen');
        Schema::rename('class_group_student', 'groep_student');

        Schema::table('groep_student', function (Blueprint $table): void {
            $table->renameColumn('class_group_id', 'groep_id');
        });
    }

    public function down(): void
    {
        Schema::table('groep_student', function (Blueprint $table): void {
            $table->renameColumn('groep_id', 'class_group_id');
        });

        Schema::rename('groep_student', 'class_group_student');
        Schema::rename('groepen', 'class_groups');
    }
};
