<?php

namespace App\Filament\Resources\Venues\Pages;

use App\Filament\Resources\Venues\VenueResource;
use App\Models\Venue;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
            Action::make('markInviteSent')
                ->label('Mark invite sent')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Mark claim invite as sent?')
                ->modalDescription('Records that this venue has already been invited, without sending an email. Use this for fisheries contacted outside the system.')
                ->action(function (): void {
                    /** @var Venue $venue */
                    $venue = $this->record;
                    $venue->markInviteSent();

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
                ->modalDescription(fn (): string => filled($this->record->contact_email)
                    ? 'An invite will be emailed to '.$this->record->contact_email.'.'
                    : 'This venue has no contact email. Add one in the wizard before sending.')
                ->action(function (): void {
                    /** @var Venue $venue */
                    $venue = $this->record;

                    if (! $venue->sendClaimInvite()) {
                        Notification::make()
                            ->title('No contact email')
                            ->body('Add a contact email before sending a claim invite.')
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Claim invite sent')
                        ->body('Emailed '.$venue->contact_email.'.')
                        ->success()
                        ->send();
                }),
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
