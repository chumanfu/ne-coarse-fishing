<?php

namespace App\Filament\Resources\VenueTactics\Pages;

use App\Filament\Resources\VenueTactics\VenueTacticResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVenueTactics extends ListRecords
{
    protected static string $resource = VenueTacticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
