<?php

namespace App\Filament\Resources\VenueTactics\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VenueTacticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('venue_id')
                    ->relationship('venue', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('fishing_session_id')
                    ->relationship('fishingSession', 'id'),
                Select::make('water_id')
                    ->relationship('water', 'name'),
                TextInput::make('peg_number'),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('fished_at'),
            ]);
    }
}
