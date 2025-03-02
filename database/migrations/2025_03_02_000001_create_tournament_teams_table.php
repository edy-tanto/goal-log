<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('seed')->nullable();
            $table->boolean('eliminated')->default(false);
            $table->unsignedInteger('current_round')->default(0);
            $table->timestamps();

            $table->unique(['tournament_id', 'team_id']);
            $table->unique(['tournament_id', 'seed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_teams');
    }
}; 