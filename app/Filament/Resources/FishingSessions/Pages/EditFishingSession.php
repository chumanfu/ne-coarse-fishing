<?php

namespace App\Filament\Resources\FishingSessions\Pages;

use App\Filament\Resources\FishingSessions\FishingSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFishingSession extends EditRecord
{
    protected static string $resource = FishingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
