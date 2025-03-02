<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentMatchResource\Pages;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\MatchStatistic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TournamentMatchResource extends Resource
{
    protected static ?string $model = TournamentMatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    
    protected static ?string $navigationGroup = 'Tournament Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Match Details')
                    ->schema([
                        Forms\Components\Select::make('tournament_id')
                            ->label('Tournament')
                            ->relationship('tournament', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('team1_id', null);
                                $set('round_id', null);
                            }),
                            
                        Forms\Components\Select::make('round_id')
                            ->label('Tournament Round')
                            ->relationship('round', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get) {
                                $tournamentId = $get('tournament_id');
                                if (!$tournamentId) return [];
                                
                                $rounds = \App\Models\TournamentRound::where('tournament_id', $tournamentId)
                                    ->pluck('name', 'id')
                                    ->toArray();
                                    
                                return $rounds;
                            })
                            ->placeholder(function (callable $get) {
                                $tournamentId = $get('tournament_id');
                                if (!$tournamentId) return 'Select a tournament first';
                                
                                $roundCount = \App\Models\TournamentRound::where('tournament_id', $tournamentId)->count();
                                
                                return $roundCount > 0 
                                    ? 'Select a round' 
                                    : 'No rounds available for this tournament';
                            }),
                            
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('team1_id')
                                    ->label('Home Team')
                                    ->options(function (callable $get) {
                                        $tournamentId = $get('tournament_id');
                                        if (!$tournamentId) return [];
                                        
                                        return Team::whereHas('tournaments', function($query) use ($tournamentId) {
                                            $query->where('tournaments.id', $tournamentId);
                                        })->pluck('name', 'id')->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(),
                                    
                                Forms\Components\Select::make('team2_id')
                                    ->label('Away Team')
                                    ->options(function (callable $get) {
                                        $tournamentId = $get('tournament_id');
                                        $team1Id = $get('team1_id');
                                        if (!$tournamentId) return [];
                                        
                                        $query = Team::whereHas('tournaments', function($q) use ($tournamentId) {
                                            $q->where('tournaments.id', $tournamentId);
                                        });
                                        
                                        if ($team1Id) {
                                            $query->where('id', '!=', $team1Id);
                                        }
                                        
                                        return $query->pluck('name', 'id')->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('match_number')
                                    ->label('Match Number')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(function () {
                                        // Get the highest match number and increment by 1
                                        $lastMatch = TournamentMatch::orderBy('match_number', 'desc')->first();
                                        return $lastMatch ? $lastMatch->match_number + 1 : 1;
                                    })
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('venue')
                                    ->label('Venue')
                                    ->maxLength(255),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('Match Date & Time')
                                    ->required(),
                                    
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'in_progress' => 'In Progress',
                                        'completed' => 'Completed',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
                            
                        Forms\Components\Textarea::make('notes')
                            ->label('Match Notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Match Result')
                    ->schema([
                        Forms\Components\Placeholder::make('match_result_description')
                            ->label('')
                            ->content('Match results can be filled in after the match is completed.')
                            ->columnSpanFull(),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('score_team1')
                                    ->label(function (callable $get) {
                                        $teamId = $get('team1_id');
                                        if (!$teamId) return 'Home Team Score';
                                        
                                        $team = Team::find($teamId);
                                        return $team ? "{$team->name} Score" : 'Home Team Score';
                                    })
                                    ->numeric()
                                    ->minValue(0),
                                    
                                Forms\Components\TextInput::make('score_team2')
                                    ->label(function (callable $get) {
                                        $teamId = $get('team2_id');
                                        if (!$teamId) return 'Away Team Score';
                                        
                                        $team = Team::find($teamId);
                                        return $team ? "{$team->name} Score" : 'Away Team Score';
                                    })
                                    ->numeric()
                                    ->minValue(0),
                            ]),
                            
                        Forms\Components\Select::make('winner_id')
                            ->label('Winner')
                            ->options(function (callable $get) {
                                $options = [];
                                $team1Id = $get('team1_id');
                                $team2Id = $get('team2_id');
                                
                                if ($team1Id) {
                                    $team = Team::find($team1Id);
                                    if ($team) {
                                        $options[$team1Id] = $team->name;
                                    }
                                }
                                
                                if ($team2Id) {
                                    $team = Team::find($team2Id);
                                    if ($team) {
                                        $options[$team2Id] = $team->name;
                                    }
                                }
                                
                                return $options;
                            })
                            ->searchable(),
                            
                        Forms\Components\Select::make('mvp_player_id')
                            ->label('MVP (Most Valuable Player)')
                            ->options(function (callable $get) {
                                $team1Id = $get('team1_id');
                                $team2Id = $get('team2_id');
                                if (!$team1Id && !$team2Id) return [];
                                
                                return Player::whereIn('team_id', array_filter([$team1Id, $team2Id]))
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable(),
                    ])
                    ->collapsed(),
                    
                Forms\Components\Section::make('Player Statistics')
                    ->schema([
                        Forms\Components\Placeholder::make('player_stats_description')
                            ->label('')
                            ->content('Player statistics can be added after the match is completed. All statistics default to zero.')
                            ->columnSpanFull(),
                            
                        Forms\Components\Repeater::make('statistics')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('team_id')
                                    ->label('Team')
                                    ->options(function (callable $get) {
                                        $options = [];
                                        $team1Id = $get('../../team1_id');
                                        $team2Id = $get('../../team2_id');
                                        
                                        if ($team1Id) {
                                            $team = Team::find($team1Id);
                                            if ($team) {
                                                $options[$team1Id] = $team->name;
                                            }
                                        }
                                        
                                        if ($team2Id) {
                                            $team = Team::find($team2Id);
                                            if ($team) {
                                                $options[$team2Id] = $team->name;
                                            }
                                        }
                                        
                                        return $options;
                                    })
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('player_id', null)),
                                    
                                Forms\Components\Select::make('player_id')
                                    ->label('Player')
                                    ->options(function (callable $get) {
                                        $teamId = $get('team_id');
                                        if (!$teamId) return [];
                                        
                                        return Player::where('team_id', $teamId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable(),
                                    
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('goals')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->suffixIcon('heroicon-m-star'),
                                            
                                        Forms\Components\TextInput::make('assists')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                            
                                        Forms\Components\TextInput::make('yellow_cards')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                            
                                        Forms\Components\TextInput::make('red_cards')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                            
                                        Forms\Components\TextInput::make('minutes_played')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(120)
                                            ->default(0),
                                            
                                        Forms\Components\TextInput::make('shots_on_target')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                            
                                        Forms\Components\TextInput::make('shots_off_target')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                            
                                        Forms\Components\TextInput::make('passes_completed')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0),
                                    ]),
                            ])
                            ->minItems(0)
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                // Ensure all numeric fields have a zero default if empty
                                $fields = ['goals', 'assists', 'yellow_cards', 'red_cards', 
                                           'minutes_played', 'shots_on_target', 
                                           'shots_off_target', 'passes_completed'];
                                
                                foreach ($fields as $field) {
                                    if (!isset($data[$field]) || $data[$field] === '') {
                                        $data[$field] = 0;
                                    }
                                }
                                
                                return $data;
                            })
                            ->columns(1)
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                $player = Player::find($state['player_id'] ?? null);
                                return $player ? $player->name : 'Player Statistics';
                            }),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('Tournament')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('round.name')
                    ->label('Round')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('team1.name')
                    ->label('Home Team')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(function (TournamentMatch $record) {
                        if ($record->score_team1 === null || $record->score_team2 === null) {
                            return 'vs';
                        }
                        
                        return "{$record->score_team1} - {$record->score_team2}";
                    }),
                    
                Tables\Columns\TextColumn::make('team2.name')
                    ->label('Away Team')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Date & Time')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('venue')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('winner.name')
                    ->label('Winner')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('mvpPlayer.name')
                    ->label('MVP')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                    ]),
            ])
            ->filters([
                Tables\Filters\Filter::make('tournament_round')
                    ->form([
                        Forms\Components\Select::make('tournament_id')
                            ->label('Tournament')
                            ->options(
                                Tournament::pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('round_id', null)),
                            
                        Forms\Components\Select::make('round_id')
                            ->label('Round')
                            ->options(function (callable $get) {
                                $tournamentId = $get('tournament_id');
                                if (!$tournamentId) return [];
                                
                                return \App\Models\TournamentRound::where('tournament_id', $tournamentId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tournament_id'],
                                fn (Builder $query, $tournamentId): Builder => $query->where('tournament_id', $tournamentId),
                            )
                            ->when(
                                $data['round_id'],
                                fn (Builder $query, $roundId): Builder => $query->where('round_id', $roundId),
                            );
                    }),
                    
                Tables\Filters\SelectFilter::make('team_filter')
                    ->label('Team')
                    ->options(Team::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value']) || empty($data['value'])) {
                            return $query;
                        }
                        
                        return $query->where(function($query) use ($data) {
                            $query->where('team1_id', $data['value'])
                                  ->orWhere('team2_id', $data['value']);
                        });
                    }),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTournamentMatches::route('/'),
            'create' => Pages\CreateTournamentMatch::route('/create'),
            'edit' => Pages\EditTournamentMatch::route('/{record}/edit'),
            'view' => Pages\ViewTournamentMatch::route('/{record}'),
        ];
    }
} 