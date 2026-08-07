<?php

namespace App\Filament\Resources\Waters\Schemas;

use App\Models\Water;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WaterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('venue_id')
                    ->relationship('venue', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('peg_count')
                    ->numeric(),
                Textarea::make('depth_info')
                    ->columnSpanFull(),
                CheckboxList::make('facilities')
                    ->label('Facilities')
                    ->options(Water::FACILITIES)
                    ->columns(2)
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
