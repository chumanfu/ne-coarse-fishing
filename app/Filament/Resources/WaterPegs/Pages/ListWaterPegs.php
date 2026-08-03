<?php

namespace App\Filament\Resources\WaterPegs\Pages;

use App\Filament\Resources\WaterPegs\WaterPegResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaterPegs extends ListRecords
{
    protected static string $resource = WaterPegResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
