<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'round_id',
        'team1_id',
        'team2_id',
        'winner_id',
        'match_number',
        'status',
        'score_team1',
        'score_team2',
        'scheduled_at',
        'venue',
        'notes',
        'mvp_player_id',
    ];

    protected $casts = [
        'tournament_id' => 'integer',
        'round_id' => 'integer',
        'team1_id' => 'integer',
        'team2_id' => 'integer',
        'winner_id' => 'integer',
        'match_number' => 'integer',
        'score_team1' => 'integer',
        'score_team2' => 'integer',
        'scheduled_at' => 'datetime',
        'mvp_player_id' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(TournamentRound::class, 'round_id');
    }

    public function team1(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }
    
    public function mvpPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'mvp_player_id');
    }
    
    public function statistics(): HasMany
    {
        return $this->hasMany(MatchStatistic::class, 'match_id');
    }
} 