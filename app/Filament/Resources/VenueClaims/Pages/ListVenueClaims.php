<?php

namespace App\Filament\Resources\VenueClaims\Pages;

use App\Filament\Resources\VenueClaims\VenueClaimResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVenueClaims extends ListRecords
{
    protected static string $resource = VenueClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
