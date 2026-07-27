<?php

namespace App\Filament\Resources\FishingSessions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FishingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')->relationship('user', 'name')->searchable()->required(),
                Select::make('venue_id')->relationship('venue', 'name')->searchable()->required(),
                Select::make('water_id')->relationship('water', 'name')->searchable(),
                DatePicker::make('fished_at')->required(),
                TextInput::make('duration_hours')->numeric(),
                TextInput::make('weather')->maxLength(255),
                TextInput::make('peg_number')->maxLength(50),
                Textarea::make('commentary')->rows(5)->columnSpanFull(),
                Textarea::make('tactics_tip')
                    ->label('Tactics tip')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
