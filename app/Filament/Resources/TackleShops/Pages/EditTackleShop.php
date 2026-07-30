<?php

namespace App\Filament\Resources\TackleShops\Pages;

use App\Filament\Resources\TackleShops\TackleShopResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTackleShop extends EditRecord
{
    protected static string $resource = TackleShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
