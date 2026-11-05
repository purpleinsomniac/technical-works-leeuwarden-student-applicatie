<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('activity_xp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('gebruikers')->cascadeOnDelete();
            $table->string('source');
            $table->unsignedBigInteger('source_id');
            $table->unsignedInteger('amount');
            $table->timestamp('created_at');
            $table->unique(['user_id', 'source', 'source_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('activity_xp'); }
};
