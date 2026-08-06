<?php

namespace App\Filament\Resources\TackleReviews\Pages;

use App\Filament\Resources\TackleReviews\TackleReviewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTackleReview extends EditRecord
{
    protected static string $resource = TackleReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
