<?php

namespace Tests\Feature;

use App\Filament\Resources\TackleShops\Pages\EditTackleShop;
use App\Mail\TackleShopClaimInvite;
use App\Models\TackleShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TackleShopClaimInviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_tackle_shops_table_has_claim_invite_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('tackle_shops', [
            'contact_email',
            'invite_sent_at',
        ]));
    }

    public function test_admin_can_mark_invite_sent_without_emailing(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $shop = TackleShop::factory()->create([
            'contact_email' => 'shop@example.com',
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditTackleShop::class, ['record' => $shop->getRouteKey()])
            ->callAction('markInviteSent')
            ->assertHasNoActionErrors();

        $this->assertNotNull($shop->fresh()->invite_sent_at);
        Mail::assertNothingSent();
    }

    public function test_admin_can_send_claim_invite_and_sets_timestamp(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $shop = TackleShop::factory()->create([
            'name' => 'Tyne Tackle',
            'contact_email' => 'owner@example.com',
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditTackleShop::class, ['record' => $shop->getRouteKey()])
            ->callAction('sendClaimInvite')
            ->assertHasNoActionErrors();

        $this->assertNotNull($shop->fresh()->invite_sent_at);

        Mail::assertSent(TackleShopClaimInvite::class, function (TackleShopClaimInvite $mail) use ($shop) {
            return $mail->hasTo('owner@example.com')
                && $mail->tackleShop->is($shop);
        });
    }

    public function test_send_claim_invite_does_nothing_without_contact_email(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $shop = TackleShop::factory()->create([
            'contact_email' => null,
            'invite_sent_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EditTackleShop::class, ['record' => $shop->getRouteKey()])
            ->callAction('sendClaimInvite')
            ->assertHasNoActionErrors();

        $this->assertNull($shop->fresh()->invite_sent_at);
        Mail::assertNothingSent();
    }

    public function test_claim_invite_email_includes_shop_page_and_claim_guidance(): void
    {
        $shop = TackleShop::factory()->create([
            'name' => 'North Tyne Tackle',
            'slug' => 'north-tyne-tackle-invite-test',
            'contact_email' => 'info@northtyne.test',
        ]);

        $mailable = new TackleShopClaimInvite($shop);
        $html = $mailable->render();

        $this->assertStringContainsString('Chris Mitchell', $html);
        $this->assertStringContainsString('North Tyne Tackle', $html);
        $this->assertStringContainsString(route('tackle-shops.show', $shop), $html);
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
