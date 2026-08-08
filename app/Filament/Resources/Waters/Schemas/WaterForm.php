<?php

namespace App\Filament\Resources\Waters\Schemas;

use Filament\Forms\Components\FileUpload;
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
                FileUpload::make('map_image_path')
                    ->label('Pond map image')
                    ->helperText('Top-down image used to place pegs. Changing it clears existing peg positions.')
                    ->image()
                    ->directory('water-maps')
                    ->disk(config('filesystems.uploads'))
                    ->visibility('public')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
