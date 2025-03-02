<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TournamentRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'name',
        'round_number',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'tournament_id' => 'integer',
        'round_number' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class, 'round_id');
    }
} 