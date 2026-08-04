<?php

namespace App\Filament\Resources\WaterPegs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WaterPegForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('water_id')
                    ->relationship('water', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('created_by')
                    ->label('Created by')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')->maxLength(255),
                TextInput::make('number')->maxLength(50),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                TextInput::make('latitude')->required()->numeric(),
                TextInput::make('longitude')->required()->numeric(),
                Toggle::make('is_verified')->default(false),
                Select::make('verified_by')
                    ->label('Verified by')
                    ->relationship('verifier', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('verified_at'),
                TextInput::make('sort_order')->required()->numeric()->default(0),
            ]);
    }
}
