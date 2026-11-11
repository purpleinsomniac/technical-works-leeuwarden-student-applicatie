<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('class_groups')) {
            Schema::create('class_groups', function (Blueprint $table): void {
                $table->id();
                $table->string('naam');
                $table->string('schooljaar', 20)->nullable();
                $table->text('omschrijving')->nullable();
                $table->foreignId('coach_id')->nullable();
                $table->timestamps();
                $table->index(['coach_id', 'naam']);
            });
        }

        Schema::table('class_groups', function (Blueprint $table): void {
            $table->foreign('coach_id', 'class_groups_coach_id_restore_fk')
                ->references('id')->on('gebruikers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Keep the repaired table and any related data intact on rollback.
    }
};
