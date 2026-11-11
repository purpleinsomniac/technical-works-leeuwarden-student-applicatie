<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('feedback', function (Blueprint $table) {
            $table->timestamp('processed_confirmed_at')->nullable();
        });
        DB::table('challenges')->insert(['title'=>'Feedback toepassen','description'=>'Verwerk ontvangen feedback in je huidige fase en laat je coach dit bevestigen.','type'=>'feedback','target'=>1,'reward'=>40]);
    }
    public function down(): void {
        Schema::table('feedback', fn (Blueprint $table) => $table->dropColumn('processed_confirmed_at'));
    }
};
