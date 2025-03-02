<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentTeamResource\Pages;

use App\Filament\Resources\TournamentTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTournamentTeam extends EditRecord
{
    protected static string $resource = TournamentTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 