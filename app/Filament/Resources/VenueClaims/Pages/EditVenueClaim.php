<?php

namespace App\Filament\Resources\VenueClaims\Pages;

use App\Filament\Resources\VenueClaims\VenueClaimResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVenueClaim extends EditRecord
{
    protected static string $resource = VenueClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
