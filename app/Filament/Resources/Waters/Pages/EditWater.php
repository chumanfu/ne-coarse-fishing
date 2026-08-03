<?php

namespace App\Filament\Resources\Waters\Pages;

use App\Filament\Resources\Waters\WaterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWater extends EditRecord
{
    protected static string $resource = WaterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
