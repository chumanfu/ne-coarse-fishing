<?php

namespace App\Filament\Resources\VenueEditRequests\Pages;

use App\Filament\Resources\VenueEditRequests\VenueEditRequestResource;
use App\Models\VenueEditRequest;
use App\Services\VenueEditRequestComparison;
use App\Services\VenuePersistenceService;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ViewVenueEditRequest extends Page
{
    use InteractsWithRecord;

    protected static string $resource = VenueEditRequestResource::class;

    protected string $view = 'filament.venue-edit-requests.compare';

    /** @var array{fields: list<array<string, mixed>>, waters: list<array<string, mixed>>} */
    public array $comparison = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        abort_unless(static::getResource()::canView($this->getRecord()), 403);

        $this->record->load(['venue.waters.species', 'user', 'reviewer']);
        $this->comparison = app(VenueEditRequestComparison::class)->build($this->record);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Review edit · '.$this->record->venue->name;
    }

    public function getHeading(): string|Htmlable
    {
        return 'Review edit · '.$this->record->venue->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->visible(fn (): bool => auth()->user()?->can('review', $this->record) ?? false)
                ->color('success')
                ->requiresConfirmation()
                ->action(function (): void {
                    app(VenuePersistenceService::class)->apply($this->record->venue, $this->record->proposed_data);
                    $this->record->update([
                        'status' => 'approved',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    $this->redirect(VenueEditRequestResource::getUrl('index'));
                }),
            Action::make('reject')
                ->visible(fn (): bool => auth()->user()?->can('review', $this->record) ?? false)
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update([
                        'status' => 'rejected',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);

                    $this->redirect(VenueEditRequestResource::getUrl('index'));
                }),
        ];
    }

    public function getRecord(): VenueEditRequest
    {
        /** @var VenueEditRequest */
        return $this->record;
    }
}
