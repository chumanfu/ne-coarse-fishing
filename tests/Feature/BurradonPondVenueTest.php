<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BurradonPondVenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_burradon_pond_is_seeded_and_showable(): void
    {
        $admin = User::query()->where('email', 'admin@nefishing.test')->first()
            ?? User::factory()->create(['email' => 'admin@nefishing.test']);

        $this->artisan('migrate', [
            '--path' => 'database/migrations/2026_08_08_161500_seed_burradon_pond.php',
            '--force' => true,
        ])->assertSuccessful();

        $venue = Venue::query()->where('slug', 'burradon-pond')->first();

        $this->assertNotNull($venue);
        $this->assertSame('Burradon Pond', $venue->name);
        $this->assertSame($admin->id, $venue->user_id);
        $this->assertTrue($venue->is_approved);
        $this->assertTrue($venue->waters()->exists());
        $this->assertStringContainsString('Camperdown', (string) $venue->address);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Burradon Pond')
            ->assertSee('Camperdown', false);

        $this->get(route('venues.index', ['q' => 'Burradon']))
            ->assertOk()
            ->assertSee('Burradon Pond');
    }
}
