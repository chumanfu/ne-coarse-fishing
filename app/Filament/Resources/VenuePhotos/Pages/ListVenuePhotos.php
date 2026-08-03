<?php

namespace App\Filament\Resources\VenuePhotos\Pages;

use App\Filament\Resources\VenuePhotos\VenuePhotoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVenuePhotos extends ListRecords
{
    protected static string $resource = VenuePhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
