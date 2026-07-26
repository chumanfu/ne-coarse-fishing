<?php

namespace App\Filament\Resources\Venues\Pages;

use App\Filament\Resources\Venues\VenueResource;
use Filament\Resources\Pages\Page;

class CreateVenue extends Page
{
    protected static string $resource = VenueResource::class;

    protected string $view = 'filament.venues.wizard';

    protected static ?string $title = 'Create venue';

    public function getHeading(): string
    {
        return 'Create venue';
    }
}
