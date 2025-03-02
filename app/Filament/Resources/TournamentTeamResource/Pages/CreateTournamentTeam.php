<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentTeamResource\Pages;

use App\Filament\Resources\TournamentTeamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTournamentTeam extends CreateRecord
{
    protected static string $resource = TournamentTeamResource::class;
} 