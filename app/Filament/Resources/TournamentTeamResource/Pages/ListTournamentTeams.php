<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentTeamResource\Pages;

use App\Filament\Resources\TournamentTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTournamentTeams extends ListRecords
{
    protected static string $resource = TournamentTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 