<?php

namespace Tests\Feature;

use App\Filament\Resources\Clubs\Pages\EditClub;
use App\Mail\ClubClaimInvite;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClubClaimInviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_clubs_table_has_claim_invite_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('clubs', [
            'contact_email',
            'invite_sent_at',
        ]));
    }

    public function test_admin_can_mark_invite_sent_without_emailing(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $club = Club::factory()->create([
            'contact_email' => 'club@example.com',
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditClub::class, ['record' => $club->getRouteKey()])
            ->callAction('markInviteSent')
            ->assertHasNoActionErrors();

        $this->assertNotNull($club->fresh()->invite_sent_at);
        Mail::assertNothingSent();
    }

    public function test_admin_can_send_claim_invite_and_sets_timestamp(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $club = Club::factory()->create([
            'name' => 'Tyne Test Anglers',
            'contact_email' => 'secretary@example.com',
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditClub::class, ['record' => $club->getRouteKey()])
            ->callAction('sendClaimInvite')
            ->assertHasNoActionErrors();

        $this->assertNotNull($club->fresh()->invite_sent_at);

        Mail::assertSent(ClubClaimInvite::class, function (ClubClaimInvite $mail) use ($club) {
            return $mail->hasTo('secretary@example.com')
                && $mail->club->is($club);
        });
    }

    public function test_send_claim_invite_does_nothing_without_contact_email(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $club = Club::factory()->create([
            'contact_email' => null,
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditClub::class, ['record' => $club->getRouteKey()])
            ->callAction('sendClaimInvite')
            ->assertHasNoActionErrors();

        $this->assertNull($club->fresh()->invite_sent_at);
        Mail::assertNothingSent();
    }

    public function test_claim_invite_email_includes_club_page_and_claim_guidance(): void
    {
        $club = Club::factory()->create([
            'name' => 'Wearside Angling Club',
            'slug' => 'wearside-angling-club',
            'contact_email' => 'info@wearside.test',
        ]);

        $mailable = new ClubClaimInvite($club);
        $html = $mailable->render();

        $this->assertStringContainsString('Chris Mitchell', $html);
        $this->assertStringContainsString('Wearside Angling Club', $html);
        $this->assertStringContainsString(route('clubs.show', $club), $html);
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
