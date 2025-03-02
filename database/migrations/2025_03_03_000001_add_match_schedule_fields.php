<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->datetime('scheduled_at')->nullable()->after('score_team2');
            $table->string('venue')->nullable()->after('scheduled_at');
            $table->text('notes')->nullable()->after('venue');
            $table->foreignId('mvp_player_id')->nullable()->after('notes')->constrained('players')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropForeign(['mvp_player_id']);
            $table->dropColumn(['scheduled_at', 'venue', 'notes', 'mvp_player_id']);
        });
    }
}; 