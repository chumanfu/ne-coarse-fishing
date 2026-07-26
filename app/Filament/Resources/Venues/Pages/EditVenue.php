<?php

namespace App\Filament\Resources\Venues\Pages;

use App\Filament\Resources\Venues\VenueResource;
use App\Models\Venue;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class EditVenue extends Page
{
    protected static string $resource = VenueResource::class;

    protected string $view = 'filament.venues.wizard';

    public Venue $record;

    public function mount(int|string $record): void
    {
        $this->record = Venue::query()->findOrFail($record);
        $this->record->load('waters.species');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Edit '.$this->record->name;
    }

    public function getHeading(): string|Htmlable
    {
        return 'Edit '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('delete')
                ->label('Delete')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->delete();
                    $this->redirect(VenueResource::getUrl('index'));
                }),
        ];
    }
}
