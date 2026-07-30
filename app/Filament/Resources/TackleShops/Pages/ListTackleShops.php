<?php

namespace App\Filament\Resources\TackleShops\Pages;

use App\Filament\Resources\TackleShops\TackleShopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTackleShops extends ListRecords
{
    protected static string $resource = TackleShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
