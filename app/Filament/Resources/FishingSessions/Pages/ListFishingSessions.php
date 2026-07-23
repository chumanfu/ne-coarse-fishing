<?php

namespace App\Filament\Resources\FishingSessions\Pages;

use App\Filament\Resources\FishingSessions\FishingSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFishingSessions extends ListRecords
{
    protected static string $resource = FishingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
