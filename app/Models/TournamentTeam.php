<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentTeam extends Pivot
{
    protected $table = 'tournament_teams';

    public $incrementing = true;

    protected $fillable = [
        'tournament_id',
        'team_id',
        'seed',
        'eliminated',
        'current_round',
    ];

    protected $casts = [
        'tournament_id' => 'integer',
        'team_id' => 'integer',
        'seed' => 'integer',
        'eliminated' => 'boolean',
        'current_round' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
} 