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

    public ?int $venueId = null;

    public function mount(int|string $record): void
    {
        // Avoid typing a public Venue $record — Livewire implicit binding uses the
        // model's slug route key and 404s when Filament URLs pass the numeric id.
        $venue = Venue::query()
            ->with('waters.species')
            ->where(function ($query) use ($record): void {
                $query->whereKey($record)->orWhere('slug', $record);
            })
            ->firstOrFail();

        abort_unless(auth()->user()?->can('update', $venue), 403);

        $this->venueId = $venue->id;
    }

    public function getRecordProperty(): Venue
    {
        return Venue::query()
            ->with('waters.species')
            ->findOrFail($this->venueId);
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
                    abort_unless(auth()->user()?->can('delete', $this->record), 403);
                    $this->record->delete();
                    $this->redirect(VenueResource::getUrl('index'));
                }),
        ];
    }
}
