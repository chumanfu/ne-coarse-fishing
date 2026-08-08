<?php

namespace App\Filament\Resources\WaterVideos\Pages;

use App\Filament\Resources\WaterVideos\WaterVideoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaterVideos extends ListRecords
{
    protected static string $resource = WaterVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
