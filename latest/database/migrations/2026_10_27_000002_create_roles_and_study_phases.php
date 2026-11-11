<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rollen', function (Blueprint $table) {
            $table->id();
            $table->string('naam', 45)->unique();
            $table->timestamps();
        });

        Schema::create('studiefases', function (Blueprint $table) {
            $table->id();
            $table->string('naam', 45)->unique();
            $table->text('omschrijving')->nullable();
            $table->timestamps();
        });

        Schema::table('gebruikers', function (Blueprint $table) {
            $table->foreignId('hoofdrol_id')
                ->nullable()
                ->constrained('rollen')
                ->restrictOnDelete();

            $table->foreignId('studiefase_id')
                ->nullable()
                ->constrained('studiefases')
                ->nullOnDelete();
        });

        Schema::create('gebruiker_rol', function (Blueprint $table) {
            $table->foreignId('rol_id')->constrained('rollen')->cascadeOnDelete();
            $table->foreignId('gebruiker_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->primary(['rol_id', 'gebruiker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gebruiker_rol');

        Schema::table('gebruikers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('studiefase_id');
            $table->dropConstrainedForeignId('hoofdrol_id');
        });

        Schema::dropIfExists('studiefases');
        Schema::dropIfExists('rollen');
    }
};
