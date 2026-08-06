<?php

namespace App\Filament\Resources\SiteAnnouncements\Pages;

use App\Filament\Resources\SiteAnnouncements\SiteAnnouncementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteAnnouncements extends ListRecords
{
    protected static string $resource = SiteAnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
