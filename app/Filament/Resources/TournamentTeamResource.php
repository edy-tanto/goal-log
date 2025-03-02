<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentTeamResource\Pages;
use App\Models\Tournament;
use App\Models\TournamentTeam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TournamentTeamResource extends Resource
{
    protected static ?string $model = TournamentTeam::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tournament_id')
                    ->relationship('tournament', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('seed')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(64),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tournament.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('team.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('seed')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('eliminated')
                    ->boolean(),
                Tables\Columns\TextColumn::make('current_round')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tournament')
                    ->relationship('tournament', 'name'),
                Tables\Filters\SelectFilter::make('team')
                    ->relationship('team', 'name'),
                Tables\Filters\TernaryFilter::make('eliminated'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListTournamentTeams::route('/'),
            'create' => Pages\CreateTournamentTeam::route('/create'),
            'edit' => Pages\EditTournamentTeam::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                // Add any global scopes to remove
            ]);
    }
} 