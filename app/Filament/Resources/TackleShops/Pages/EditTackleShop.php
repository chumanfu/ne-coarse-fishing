<?php

namespace App\Filament\Resources\TackleShops\Pages;

use App\Filament\Resources\TackleShops\TackleShopResource;
use App\Models\TackleShop;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTackleShop extends EditRecord
{
    protected static string $resource = TackleShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markInviteSent')
                ->label('Mark invite sent')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Mark claim invite as sent?')
                ->modalDescription('Records that this tackle shop has already been invited, without sending an email. Use this for shops contacted outside the system.')
                ->action(function (): void {
                    /** @var TackleShop $shop */
                    $shop = $this->getRecord();
                    $shop->markInviteSent();
                    $this->refreshFormData(['invite_sent_at']);

                    Notification::make()
                        ->title('Invite marked as sent')
                        ->success()
                        ->send();
                }),
            Action::make('sendClaimInvite')
                ->label('Send claim invite')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Send claim invite email?')
                ->modalDescription(fn (): string => filled($this->getRecord()->contact_email)
                    ? 'An invite will be emailed to '.$this->getRecord()->contact_email.'.'
                    : 'This tackle shop has no contact email. Add one on the form before sending.')
                ->action(function (): void {
                    /** @var TackleShop $shop */
                    $shop = $this->getRecord();

                    if (! $shop->sendClaimInvite()) {
                        Notification::make()
                            ->title('No contact email')
                            ->body('Add a contact email before sending a claim invite.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->refreshFormData(['invite_sent_at']);

                    Notification::make()
                        ->title('Claim invite sent')
                        ->body('Emailed '.$shop->contact_email.'.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
