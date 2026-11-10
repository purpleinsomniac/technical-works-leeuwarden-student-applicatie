<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicatie_instellingen', function (Blueprint $table): void {
            $table->string('application_accent_color', 7)->default('#009b87');
            $table->string('application_style', 16)->default('standard');
        });
    }

    public function down(): void
    {
        Schema::table('applicatie_instellingen', function (Blueprint $table): void {
            $table->dropColumn(['application_accent_color', 'application_style']);
        });
    }
};
