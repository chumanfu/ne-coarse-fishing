<?php

namespace App\Filament\Resources\Waters\Pages;

use App\Filament\Resources\Waters\WaterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaters extends ListRecords
{
    protected static string $resource = WaterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
