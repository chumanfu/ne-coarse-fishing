<?php

namespace App\Filament\Resources\WaterPegs\Pages;

use App\Filament\Resources\WaterPegs\WaterPegResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaterPeg extends EditRecord
{
    protected static string $resource = WaterPegResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
