<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentMatchResource\Pages;

use App\Filament\Resources\TournamentMatchResource;
use App\Models\Player;
use App\Models\Team;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTournamentMatch extends ViewRecord
{
    protected static string $resource = TournamentMatchResource::class;

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
                Section::make('Match Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tournament.name')
                                    ->label('Tournament'),
                                TextEntry::make('round.name')
                                    ->label('Round'),
                                TextEntry::make('scheduled_at')
                                    ->label('Date & Time')
                                    ->dateTime(),
                                TextEntry::make('venue')
                                    ->label('Venue'),
                            ]),
                            
                        Split::make([
                            Group::make([
                                TextEntry::make('team1.name')
                                    ->label('Home Team')
                                    ->weight('bold')
                                    ->size('xl'),
                                TextEntry::make('score_team1')
                                    ->label('Score')
                                    ->weight('bold')
                                    ->size('2xl'),
                            ])
                            ->grow(false),
                            
                            TextEntry::make('vs')
                                ->state('vs')
                                ->weight('bold')
                                ->size('lg')
                                ->alignCenter(),
                                
                            Group::make([
                                TextEntry::make('team2.name')
                                    ->label('Away Team')
                                    ->weight('bold')
                                    ->size('xl'),
                                TextEntry::make('score_team2')
                                    ->label('Score')
                                    ->weight('bold')
                                    ->size('2xl'),
                            ])
                            ->grow(false),
                        ])
                        ->from('md'),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Match Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'gray',
                                        'in_progress' => 'warning',
                                        'completed' => 'success',
                                    }),
                                TextEntry::make('winner.name')
                                    ->label('Winner')
                                    ->visible(fn ($record) => $record->winner_id !== null)
                                    ->weight('bold')
                                    ->color('success'),
                                TextEntry::make('mvpPlayer.name')
                                    ->label('MVP')
                                    ->visible(fn ($record) => $record->mvp_player_id !== null)
                                    ->weight('bold')
                                    ->color('primary'),
                            ]),
                            
                        TextEntry::make('notes')
                            ->label('Match Notes')
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('Player Statistics')
                    ->schema([
                        RepeatableEntry::make('statistics')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('player.name')
                                            ->label('Player')
                                            ->weight('bold'),
                                        TextEntry::make('team.name')
                                            ->label('Team'),
                                        TextEntry::make('goals')
                                            ->label('Goals'),
                                        TextEntry::make('assists')
                                            ->label('Assists'),
                                        TextEntry::make('yellow_cards')
                                            ->label('Yellow Cards'),
                                        TextEntry::make('red_cards')
                                            ->label('Red Cards'),
                                        TextEntry::make('minutes_played')
                                            ->label('Minutes Played'),
                                        TextEntry::make('shots_on_target')
                                            ->label('Shots On Target'),
                                    ]),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
} 