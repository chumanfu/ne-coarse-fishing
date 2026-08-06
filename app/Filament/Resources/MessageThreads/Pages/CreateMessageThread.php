<?php

namespace App\Filament\Resources\MessageThreads\Pages;

use App\Filament\Resources\MessageThreads\MessageThreadResource;
use App\Models\User;
use App\Services\MessagingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMessageThread extends CreateRecord
{
    protected static string $resource = MessageThreadResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $recipient = User::query()->findOrFail($data['user_id']);

        return app(MessagingService::class)->startWithUser(
            admin: auth()->user(),
            recipient: $recipient,
            subject: $data['subject'],
            body: $data['body'],
        );
    }

    protected function getRedirectUrl(): string
    {
        return MessageThreadResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
