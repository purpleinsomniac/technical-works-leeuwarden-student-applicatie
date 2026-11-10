<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gebruikers', function (Blueprint $table): void {
            $table->string('studentnummer', 20)->nullable()->unique();
            $table->unsignedTinyInteger('leerjaar')->nullable();
        });

        DB::table('gebruikers')
            ->where('rol', 'student')
            ->orderBy('id')
            ->eachById(function (object $student): void {
                DB::table('gebruikers')
                    ->where('id', $student->id)
                    ->update(['studentnummer' => 'STU'.str_pad((string) $student->id, 6, '0', STR_PAD_LEFT)]);
            });
    }

    public function down(): void
    {
        Schema::table('gebruikers', function (Blueprint $table): void {
            $table->dropColumn(['studentnummer', 'leerjaar']);
        });
    }
};
