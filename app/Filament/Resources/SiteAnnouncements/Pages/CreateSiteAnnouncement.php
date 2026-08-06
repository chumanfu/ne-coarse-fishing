<?php

namespace App\Filament\Resources\SiteAnnouncements\Pages;

use App\Filament\Resources\SiteAnnouncements\SiteAnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteAnnouncement extends CreateRecord
{
    protected static string $resource = SiteAnnouncementResource::class;
}
