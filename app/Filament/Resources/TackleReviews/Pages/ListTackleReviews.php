<?php

namespace App\Filament\Resources\TackleReviews\Pages;

use App\Filament\Resources\TackleReviews\TackleReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTackleReviews extends ListRecords
{
    protected static string $resource = TackleReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
