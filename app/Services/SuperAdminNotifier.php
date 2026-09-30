<?php

namespace App\Services;

use App\Filament\Resources\ClubClaims\ClubClaimResource;
use App\Filament\Resources\TackleShopClaims\TackleShopClaimResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\VenueClaims\VenueClaimResource;
use App\Mail\AdminOwnershipClaimNotification;
use App\Mail\AdminUserSignupNotification;
use App\Models\ClubClaim;
use App\Models\TackleShopClaim;
use App\Models\User;
use App\Models\VenueClaim;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SuperAdminNotifier
{
    public function userSignedUp(User $user): void
    {
        $this->send(
            new AdminUserSignupNotification(
                user: $user,
                reviewUrl: UserResource::getUrl('edit', ['record' => $user]),
            ),
            except: $user,
        );
    }

    public function venueClaimed(VenueClaim $claim): void
    {
        $claim->loadMissing('venue', 'user');

        if (! $claim->user || ! $claim->venue) {
            return;
        }

        $this->sendClaim(
            listingKind: 'venue',
            listingName: $claim->venue->name,
            claimant: $claim->user,
            message: $claim->message,
            reviewUrl: VenueClaimResource::getUrl('index'),
        );
    }

    public function clubClaimed(ClubClaim $claim): void
    {
        $claim->loadMissing('club', 'user');

        if (! $claim->user || ! $claim->club) {
            return;
        }

        $this->sendClaim(
            listingKind: 'club',
            listingName: $claim->club->name,
            claimant: $claim->user,
            message: $claim->message,
            reviewUrl: ClubClaimResource::getUrl('index'),
        );
    }

    public function tackleShopClaimed(TackleShopClaim $claim): void
    {
        $claim->loadMissing('tackleShop', 'user');

        if (! $claim->user || ! $claim->tackleShop) {
            return;
        }

        $this->sendClaim(
            listingKind: 'tackle shop',
            listingName: $claim->tackleShop->name,
            claimant: $claim->user,
            message: $claim->message,
            reviewUrl: TackleShopClaimResource::getUrl('index'),
        );
    }

    private function sendClaim(
        string $listingKind,
        string $listingName,
        User $claimant,
        ?string $message,
        string $reviewUrl,
    ): void {
        $this->send(
            new AdminOwnershipClaimNotification(
                listingKind: $listingKind,
                listingName: $listingName,
                claimant: $claimant,
                message: $message,
                reviewUrl: $reviewUrl,
            ),
            except: $claimant,
        );
    }

    private function send(Mailable $mailable, ?User $except = null): void
    {
        foreach ($this->superAdmins($except) as $admin) {
            if (! filled($admin->email)) {
                continue;
            }

            Mail::to($admin)->send(clone $mailable);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function superAdmins(?User $except = null): Collection
    {
        return User::role('super_admin')
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->get();
    }
}
