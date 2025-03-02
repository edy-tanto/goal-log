<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentMatchResource\Pages;

use App\Filament\Resources\TournamentMatchResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Form;

class CreateTournamentMatch extends CreateRecord
{
    protected static string $resource = TournamentMatchResource::class;
    
    public function form(Form $form): Form
    {
        // Get the parent form schema
        $form = parent::form($form);
        
        // Get all sections except Player Statistics and Match Result
        $updatedSections = collect($form->getComponents())
            ->filter(function ($section) {
                $heading = $section->getHeading();
                
                // Filter out both Player Statistics and Match Result sections
                return !in_array($heading, [
                    'Player Statistics',
                    'Match Result'
                ]);
            })
            ->toArray();
        
        // Return the form with the filtered sections
        return $form->components($updatedSections);
    }
} 