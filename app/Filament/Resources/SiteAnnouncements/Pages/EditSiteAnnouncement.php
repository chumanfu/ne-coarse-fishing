<?php

namespace App\Filament\Resources\SiteAnnouncements\Pages;

use App\Filament\Resources\SiteAnnouncements\SiteAnnouncementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteAnnouncement extends EditRecord
{
    protected static string $resource = SiteAnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
