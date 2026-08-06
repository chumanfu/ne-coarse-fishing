<?php

namespace App\Filament\Resources\ClubEditRequests\Pages;

use App\Filament\Resources\ClubEditRequests\ClubEditRequestResource;
use App\Models\ClubEditRequest;
use App\Services\ClubEditRequestComparison;
use App\Services\ClubPersistenceService;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ViewClubEditRequest extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ClubEditRequestResource::class;

    protected string $view = 'filament.club-edit-requests.compare';

    /** @var list<array<string, mixed>> */
    public array $comparison = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        abort_unless(static::getResource()::canView($this->getRecord()), 403);

        $this->record->load(['club', 'user', 'reviewer']);
        $this->comparison = app(ClubEditRequestComparison::class)->build($this->record);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Review club edit · '.$this->record->club->name;
    }

    public function getHeading(): string|Htmlable
    {
        return $this->getTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->visible(fn (): bool => auth()->user()?->can('review', $this->record) ?? false)
                ->color('success')
                ->requiresConfirmation()
                ->action(function (): void {
                    app(ClubPersistenceService::class)->apply($this->record->club, $this->record->proposed_data);
                    $this->record->update([
                        'status' => 'approved',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                    $this->redirect(ClubEditRequestResource::getUrl('index'));
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
                    $this->redirect(ClubEditRequestResource::getUrl('index'));
                }),
        ];
    }

    public function getRecord(): ClubEditRequest
    {
        /** @var ClubEditRequest */
        return $this->record;
    }
}
