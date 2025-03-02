<?php
declare(strict_types=1);

namespace App\Filament\Resources\TeamResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class PlayersRelationManager extends RelationManager
{
    protected static string $relationship = 'players';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('number')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(99),
                            
                        Forms\Components\Select::make('position')
                            ->options([
                                'Goalkeeper' => 'Goalkeeper',
                                'Defender' => 'Defender',
                                'Midfielder' => 'Midfielder',
                                'Forward' => 'Forward',
                            ]),
                            
                        SpatieMediaLibraryFileUpload::make('photo')
                            ->collection('photo')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Personal Details')
                    ->schema([
                        Forms\Components\DatePicker::make('date_of_birth'),
                        
                        Forms\Components\TextInput::make('nationality')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('height')
                            ->label('Height (cm)')
                            ->numeric()
                            ->minValue(100)
                            ->maxValue(250),
                            
                        Forms\Components\TextInput::make('weight')
                            ->label('Weight (kg)')
                            ->numeric()
                            ->minValue(30)
                            ->maxValue(150),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')
                    ->collection('photo')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('number')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('position')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('nationality')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
