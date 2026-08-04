<?php

namespace App\Filament\Resources\VenuePhotos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VenuePhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('venue_id')
                    ->relationship('venue', 'name')
                    ->required(),
                FileUpload::make('image_path')
                    ->image()
                    ->disk(config('filesystems.uploads'))
                    ->directory('venue-photos')
                    ->visibility(config('filesystems.uploads') === 'public' ? 'public' : 'private')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
