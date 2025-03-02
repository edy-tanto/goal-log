<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\TournamentRound;
use Illuminate\Database\Seeder;

class TournamentRoundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tournaments = Tournament::all();

        foreach ($tournaments as $tournament) {
            // Calculate number of rounds based on team count
            $teamCount = $tournament->team_count;
            $roundCount = (int) ceil(log($teamCount, 2));

            // Create rounds for the tournament
            for ($roundNumber = 1; $roundNumber <= $roundCount; $roundNumber++) {
                TournamentRound::create([
                    'tournament_id' => $tournament->id,
                    'name' => $this->getRoundName($roundNumber, $roundCount),
                    'round_number' => $roundNumber,
                    'status' => $roundNumber === 1 ? 'in_progress' : 'pending',
                    'start_date' => $tournament->start_date,
                    'end_date' => null,
                ]);
            }

            // Update tournament status if needed
            if ($tournament->current_round === 0) {
                $tournament->update([
                    'current_round' => 1
                ]);
            }
        }
    }

    private function getRoundName(int $roundNumber, int $totalRounds): string
    {
        return match ($totalRounds - $roundNumber) {
            0 => 'Finals',
            1 => 'Semi-Finals',
            2 => 'Quarter-Finals',
            default => 'Round of ' . pow(2, $totalRounds - $roundNumber + 1),
        };
    }
} 