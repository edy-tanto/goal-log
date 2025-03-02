<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('round_id')->constrained('tournament_rounds')->cascadeOnDelete();
            $table->foreignId('team1_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('team2_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedInteger('match_number');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->integer('score_team1')->nullable();
            $table->integer('score_team2')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'round_id', 'match_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
    }
}; 