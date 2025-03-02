<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MatchStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'team_id',
        'player_id',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
        'minutes_played',
        'shots_on_target',
        'shots_off_target',
        'passes_completed',
    ];

    protected $casts = [
        'match_id' => 'integer',
        'team_id' => 'integer',
        'player_id' => 'integer',
        'goals' => 'integer',
        'assists' => 'integer',
        'yellow_cards' => 'integer',
        'red_cards' => 'integer',
        'minutes_played' => 'integer',
        'shots_on_target' => 'integer',
        'shots_off_target' => 'integer',
        'passes_completed' => 'integer',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
} 