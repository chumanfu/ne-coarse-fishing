<?php

namespace App\Filament\Resources\VenueTactics\Pages;

use App\Filament\Resources\VenueTactics\VenueTacticResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVenueTactic extends EditRecord
{
    protected static string $resource = VenueTacticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
