<?php

namespace App\Filament\Resources\TackleShopEditRequests\Pages;

use App\Filament\Resources\TackleShopEditRequests\TackleShopEditRequestResource;
use App\Models\TackleShopEditRequest;
use App\Services\TackleShopEditRequestComparison;
use App\Services\TackleShopPersistenceService;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ViewTackleShopEditRequest extends Page
{
    use InteractsWithRecord;

    protected static string $resource = TackleShopEditRequestResource::class;

    protected string $view = 'filament.tackle-shop-edit-requests.compare';

    /** @var list<array<string, mixed>> */
    public array $comparison = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        abort_unless(static::getResource()::canView($this->getRecord()), 403);

        $this->record->load(['tackleShop', 'user', 'reviewer']);
        $this->comparison = app(TackleShopEditRequestComparison::class)->build($this->record);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Review shop edit · '.$this->record->tackleShop->name;
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
                    app(TackleShopPersistenceService::class)->apply($this->record->tackleShop, $this->record->proposed_data);
                    $this->record->update([
                        'status' => 'approved',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                    $this->redirect(TackleShopEditRequestResource::getUrl('index'));
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
                    $this->redirect(TackleShopEditRequestResource::getUrl('index'));
                }),
        ];
    }

    public function getRecord(): TackleShopEditRequest
    {
        /** @var TackleShopEditRequest */
        return $this->record;
    }
}
