<?php

namespace App\Filament\Resources\MessageThreads\Pages;

use App\Filament\Resources\MessageThreads\MessageThreadResource;
use App\Models\MessageThread;
use App\Services\MessagingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ViewMessageThread extends Page
{
    use InteractsWithRecord;

    protected static string $resource = MessageThreadResource::class;

    protected string $view = 'filament.message-threads.view';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        abort_unless(static::getResource()::canAccess(), 403);

        $this->record->load(['messages.user', 'user']);
        $this->record->markReadByAdmin();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->record->subject;
    }

    public function getHeading(): string|Htmlable
    {
        return $this->record->subject;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply')
                ->visible(fn (): bool => ! $this->record->isClosed())
                ->form([
                    Textarea::make('body')
                        ->label('Your reply')
                        ->required()
                        ->rows(6)
                        ->maxLength(5000),
                ])
                ->action(function (array $data): void {
                    app(MessagingService::class)->reply(
                        $this->record,
                        auth()->user(),
                        $data['body'],
                        asAdmin: true,
                    );

                    Notification::make()
                        ->title('Reply sent')
                        ->success()
                        ->send();

                    $this->record->refresh()->load(['messages.user', 'user']);
                }),
            Action::make('close')
                ->label('Close conversation')
                ->color('gray')
                ->visible(fn (): bool => ! $this->record->isClosed())
                ->requiresConfirmation()
                ->action(function (): void {
                    app(MessagingService::class)->close($this->record);
                    $this->record->refresh();
                }),
            Action::make('reopen')
                ->label('Reopen')
                ->visible(fn (): bool => $this->record->isClosed())
                ->action(function (): void {
                    app(MessagingService::class)->reopen($this->record);
                    $this->record->refresh();
                }),
        ];
    }

    public function getRecord(): MessageThread
    {
        /** @var MessageThread */
        return $this->record;
    }
}
