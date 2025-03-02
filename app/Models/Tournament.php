<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'team_count',
        'current_round',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'team_count' => 'integer',
        'current_round' => 'integer',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'tournament_teams')
            ->using(TournamentTeam::class)
            ->withPivot(['seed', 'eliminated', 'current_round'])
            ->withTimestamps();
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(TournamentRound::class);
    }
} 