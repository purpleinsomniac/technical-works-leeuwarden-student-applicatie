<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('opdracht_leeruitkomst', fn (Blueprint $table) => $table->unsignedInteger('max_points')->default(0));
    }
    public function down(): void {
        Schema::table('opdracht_leeruitkomst', fn (Blueprint $table) => $table->dropColumn('max_points'));
    }
};
