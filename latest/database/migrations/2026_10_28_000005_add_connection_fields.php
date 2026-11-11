<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opdrachten', function (Blueprint $table) {
            $table->string('type', 80)->default('Opdracht')->after('onderwerp');
        });

        Schema::table('feedback', function (Blueprint $table) {
            $table->string('persoon')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn('persoon');
        });

        Schema::table('opdrachten', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
