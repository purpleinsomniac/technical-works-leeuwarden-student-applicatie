<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicatie_instellingen', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedSmallInteger('max_upload_size_mb')->default(10);
            $table->json('allowed_upload_extensions');
            $table->boolean('maintenance_mode')->default(false);
            $table->timestamps();
        });

        DB::table('applicatie_instellingen')->insert([
            'id' => 1,
            'max_upload_size_mb' => 10,
            'allowed_upload_extensions' => json_encode(['pdf', 'docx', 'jpg', 'jpeg', 'png']),
            'maintenance_mode' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('applicatie_instellingen');
    }
};
