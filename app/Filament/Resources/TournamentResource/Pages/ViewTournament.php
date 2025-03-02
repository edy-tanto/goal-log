<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use App\Models\TournamentMatch;
use App\Models\TournamentTeam;
use App\Services\TournamentService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Tables\Table;
use Filament\Tables;

class ViewTournament extends ViewRecord
{
    protected static string $resource = TournamentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Tournament Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('team_count'),
                                TextEntry::make('current_round'),
                                TextEntry::make('start_date')
                                    ->dateTime(),
                                TextEntry::make('end_date')
                                    ->dateTime(),
                            ]),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function getMatchesTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('update_score')
            ->form([
                TextInput::make('score_team1')
                    ->label('Team 1 Score')
                    ->numeric()
                    ->required(),
                TextInput::make('score_team2')
                    ->label('Team 2 Score')
                    ->numeric()
                    ->required(),
                Select::make('winner_id')
                    ->label('Winner')
                    ->options(function (TournamentMatch $record): array {
                        return [
                            $record->team1_id => $record->team1?->name ?? 'Team 1',
                            $record->team2_id => $record->team2?->name ?? 'Team 2',
                        ];
                    })
                    ->required(),
            ])
            ->action(function (TournamentMatch $record, array $data, TournamentService $service): void {
                $record->update([
                    'score_team1' => $data['score_team1'],
                    'score_team2' => $data['score_team2'],
                ]);

                $winner = TournamentTeam::find($data['winner_id']);
                if ($winner) {
                    $service->advanceTeam($record, $winner);
                }
            })
            ->visible(fn (TournamentMatch $record): bool => 
                $record->status !== 'completed' && 
                $record->team1_id && 
                $record->team2_id
            )
            ->modalHeading('Update Match Score')
            ->modalDescription('Enter the final scores and select the winner.')
            ->requiresConfirmation();
    }
} 