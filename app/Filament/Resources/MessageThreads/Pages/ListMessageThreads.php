<?php

namespace App\Filament\Resources\MessageThreads\Pages;

use App\Filament\Resources\MessageThreads\MessageThreadResource;
use App\Models\User;
use App\Services\MessagingService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMessageThreads extends ListRecords
{
    protected static string $resource = MessageThreadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('broadcast')
                ->label('Message all users')
                ->icon('heroicon-o-megaphone')
                ->color('warning')
                ->form([
                    TextInput::make('subject')
                        ->required()
                        ->maxLength(160),
                    Textarea::make('body')
                        ->label('Message')
                        ->required()
                        ->rows(8)
                        ->maxLength(5000),
                ])
                ->modalHeading('Message all users')
                ->modalDescription(fn (): string => 'This creates an inbox conversation and email for each of the '
                    .User::query()->whereKeyNot(auth()->id())->count()
                    .' registered users (excluding you).')
                ->modalSubmitActionLabel('Send to everyone')
                ->requiresConfirmation()
                ->action(function (array $data): void {
                    $count = app(MessagingService::class)->broadcastToAllUsers(
                        admin: auth()->user(),
                        subject: $data['subject'],
                        body: $data['body'],
                    );

                    Notification::make()
                        ->title('Broadcast sent')
                        ->body("Messaged {$count} user".($count === 1 ? '' : 's').'.')
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->label('Message a user'),
        ];
    }
}
