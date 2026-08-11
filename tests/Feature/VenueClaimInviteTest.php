<?php

namespace Tests\Feature;

use App\Filament\Resources\Venues\Pages\EditVenue;
use App\Mail\VenueClaimInvite;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VenueClaimInviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_venues_table_has_claim_invite_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('venues', [
            'contact_email',
            'invite_sent_at',
        ]));
    }

    public function test_admin_can_mark_invite_sent_without_emailing(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $venue = Venue::factory()->create([
            'contact_email' => 'venue@example.com',
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditVenue::class, ['record' => $venue->getKey()])
            ->callAction('markInviteSent')
            ->assertHasNoActionErrors();

        $this->assertNotNull($venue->fresh()->invite_sent_at);
        Mail::assertNothingSent();
    }

    public function test_admin_can_send_claim_invite_and_sets_timestamp(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $venue = Venue::factory()->create([
            'name' => 'Test Lakes',
            'contact_email' => 'manager@example.com',
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditVenue::class, ['record' => $venue->getKey()])
            ->callAction('sendClaimInvite')
            ->assertHasNoActionErrors();

        $this->assertNotNull($venue->fresh()->invite_sent_at);

        Mail::assertSent(VenueClaimInvite::class, function (VenueClaimInvite $mail) use ($venue) {
            return $mail->hasTo('manager@example.com')
                && $mail->venue->is($venue);
        });
    }

    public function test_send_claim_invite_does_nothing_without_contact_email(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $venue = Venue::factory()->create([
            'contact_email' => null,
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditVenue::class, ['record' => $venue->getKey()])
            ->callAction('sendClaimInvite')
            ->assertHasNoActionErrors();

        $this->assertNull($venue->fresh()->invite_sent_at);
        Mail::assertNothingSent();
    }

    public function test_claim_invite_email_includes_venue_page_and_claim_guidance(): void
    {
        $venue = Venue::factory()->create([
            'name' => 'Wearside Lakes',
            'slug' => 'wearside-lakes',
            'contact_email' => 'info@wearside.test',
        ]);

        $mailable = new VenueClaimInvite($venue);
        $html = $mailable->render();

        $this->assertStringContainsString('Chris Mitchell', $html);
        $this->assertStringContainsString('Wearside Lakes', $html);
        $this->assertStringContainsString(route('venues.show', $venue), $html);
        $this->assertStringContainsString(route('register'), $html);
        $this->assertStringContainsString(route('login'), $html);
        $this->assertStringContainsString('Claim ownership', $html);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        Role::findOrCreate('super_admin');
        $admin->assignRole('super_admin');

        return $admin;
    }
}
