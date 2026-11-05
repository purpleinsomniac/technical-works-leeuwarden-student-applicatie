<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leeruitkomsten', function (Blueprint $table) {
            $table->text('normwaarden')->nullable();
        });
        Schema::table('voortgang', function (Blueprint $table) {
            $table->text('normwaarden')->nullable();
        });

        DB::table('leeruitkomsten')
            ->select('id', 'normuren')
            ->orderBy('id')
            ->chunkById(500, function ($outcomes): void {
                foreach ($outcomes as $outcome) {
                    DB::table('leeruitkomsten')
                        ->where('id', $outcome->id)
                        ->update(['normwaarden' => $outcome->normuren === null ? null : (string) $outcome->normuren]);
                }
            });
        DB::table('voortgang')
            ->select('id', 'normuren')
            ->orderBy('id')
            ->chunkById(500, function ($progressRows): void {
                foreach ($progressRows as $progress) {
                    DB::table('voortgang')
                        ->where('id', $progress->id)
                        ->update(['normwaarden' => $progress->normuren === null ? null : (string) $progress->normuren]);
                }
            });

        Schema::table('leeruitkomsten', function (Blueprint $table) {
            $table->dropColumn('normuren');
        });
        Schema::table('voortgang', function (Blueprint $table) {
            $table->dropColumn('normuren');
        });
    }

    public function down(): void
    {
        Schema::table('leeruitkomsten', function (Blueprint $table) {
            $table->unsignedInteger('normuren')->nullable();
        });
        Schema::table('voortgang', function (Blueprint $table) {
            $table->unsignedInteger('normuren')->nullable();
        });

        DB::table('leeruitkomsten')
            ->select('id', 'normwaarden')
            ->orderBy('id')
            ->chunkById(500, function ($outcomes): void {
                foreach ($outcomes as $outcome) {
                    $value = trim((string) $outcome->normwaarden);
                    $hours = $value !== '' && ctype_digit($value) ? (int) $value : null;

                    DB::table('leeruitkomsten')
                        ->where('id', $outcome->id)
                        ->update(['normuren' => $hours]);
                }
            });
        DB::table('voortgang')
            ->select('id', 'normwaarden')
            ->orderBy('id')
            ->chunkById(500, function ($progressRows): void {
                foreach ($progressRows as $progress) {
                    $value = trim((string) $progress->normwaarden);
                    $hours = $value !== '' && ctype_digit($value) ? (int) $value : null;

                    DB::table('voortgang')
                        ->where('id', $progress->id)
                        ->update(['normuren' => $hours]);
                }
            });

        Schema::table('leeruitkomsten', function (Blueprint $table) {
            $table->dropColumn('normwaarden');
        });
        Schema::table('voortgang', function (Blueprint $table) {
            $table->dropColumn('normwaarden');
        });
    }
};
