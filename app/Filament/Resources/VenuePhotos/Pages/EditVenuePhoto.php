<?php

namespace App\Filament\Resources\VenuePhotos\Pages;

use App\Filament\Resources\VenuePhotos\VenuePhotoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVenuePhoto extends EditRecord
{
    protected static string $resource = VenuePhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
