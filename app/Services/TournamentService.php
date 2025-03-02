<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentTeam;
use App\Models\TournamentRound;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TournamentService
{
    public function generateBrackets(Tournament $tournament): void
    {
        $teamCount = $tournament->teams()->count();
        
        if ($teamCount < 2) {
            throw new InvalidArgumentException('Tournament must have at least 2 teams');
        }

        // Calculate the number of rounds needed
        $roundCount = (int) ceil(log($teamCount, 2));
        $perfectBracketSize = pow(2, $roundCount);

        // Get seeded teams
        $teams = $tournament->teams()
            ->orderBy('seed')
            ->get();

        // Create rounds
        for ($roundNumber = 1; $roundNumber <= $roundCount; $roundNumber++) {
            $round = TournamentRound::create([
                'tournament_id' => $tournament->id,
                'name' => $this->getRoundName($roundNumber, $roundCount),
                'round_number' => $roundNumber,
                'status' => $roundNumber === 1 ? 'in_progress' : 'pending',
            ]);

            if ($roundNumber === 1) {
                $this->generateFirstRoundMatches($round, $teams, $perfectBracketSize);
            } else {
                $this->generateAdvancementMatches($round, pow(2, $roundCount - $roundNumber + 1) / 2);
            }
        }

        $tournament->update([
            'status' => 'in_progress',
            'current_round' => 1,
        ]);
    }

    private function generateFirstRoundMatches(TournamentRound $round, Collection $teams, int $perfectBracketSize): void
    {
        $matchCount = $perfectBracketSize / 2;
        $byeCount = $perfectBracketSize - $teams->count();

        for ($i = 0; $i < $matchCount; $i++) {
            $team1Index = $this->getFirstRoundTeamIndex($i, $matchCount);
            $team2Index = $this->getFirstRoundTeamIndex($matchCount - 1 - $i, $matchCount);

            $team1 = $teams->get($team1Index);
            $team2 = $teams->get($team2Index);

            TournamentMatch::create([
                'tournament_id' => $round->tournament_id,
                'round_id' => $round->id,
                'team1_id' => $team1?->id,
                'team2_id' => $team2?->id,
                'match_number' => $i + 1,
                'status' => ($team1 && $team2) ? 'pending' : 'completed',
                'winner_id' => (!$team2 && $team1) ? $team1->id : null,
            ]);
        }
    }

    private function generateAdvancementMatches(TournamentRound $round, int $matchCount): void
    {
        for ($i = 0; $i < $matchCount; $i++) {
            TournamentMatch::create([
                'tournament_id' => $round->tournament_id,
                'round_id' => $round->id,
                'match_number' => $i + 1,
                'status' => 'pending',
            ]);
        }
    }

    private function getFirstRoundTeamIndex(int $position, int $totalMatches): int
    {
        // Implement seeding logic (1 vs 16, 2 vs 15, etc.)
        return $position;
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

    public function advanceTeam(TournamentMatch $match, TournamentTeam $winner): void
    {
        if (!in_array($winner->id, [$match->team1_id, $match->team2_id])) {
            throw new InvalidArgumentException('Winner must be one of the teams in the match');
        }

        $match->update([
            'winner_id' => $winner->id,
            'status' => 'completed',
        ]);

        // Get the next match in the next round
        $nextRound = TournamentRound::where('tournament_id', $match->tournament_id)
            ->where('round_number', '>', $match->round->round_number)
            ->orderBy('round_number')
            ->first();

        if ($nextRound) {
            $nextMatchNumber = ceil($match->match_number / 2);
            $nextMatch = TournamentMatch::where('round_id', $nextRound->id)
                ->where('match_number', $nextMatchNumber)
                ->first();

            if ($nextMatch) {
                $isFirstTeam = $match->match_number % 2 !== 0;
                $updateData = $isFirstTeam ? ['team1_id' => $winner->id] : ['team2_id' => $winner->id];
                $nextMatch->update($updateData);
            }
        }

        // Check if current round is complete
        $incompleteMatches = TournamentMatch::where('round_id', $match->round_id)
            ->where('status', '!=', 'completed')
            ->count();

        if ($incompleteMatches === 0) {
            $match->round->update(['status' => 'completed']);
            
            if ($nextRound) {
                $nextRound->update(['status' => 'in_progress']);
                $match->tournament->update(['current_round' => $nextRound->round_number]);
            } else {
                $match->tournament->update(['status' => 'completed']);
            }
        }

        // Update eliminated status for the losing team
        $loser = TournamentTeam::find(
            $match->team1_id === $winner->id ? $match->team2_id : $match->team1_id
        );
        
        if ($loser) {
            $loser->update(['eliminated' => true]);
        }
    }
} 