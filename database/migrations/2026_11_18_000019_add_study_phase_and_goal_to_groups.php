<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groepen', function (Blueprint $table): void {
            $table->foreignId('studiefases_id')
                ->nullable()
                ->constrained('studiefases')
                ->nullOnDelete();
            $table->integer('doel')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('groepen', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('studiefases_id');
            $table->dropColumn('doel');
        });
    }
};
