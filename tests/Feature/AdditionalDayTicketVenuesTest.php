<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdditionalDayTicketVenuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_venues_are_showable(): void
    {
        $admin = User::query()->where('email', 'admin@nefishing.test')->first()
            ?? User::factory()->create(['email' => 'admin@nefishing.test']);

        $this->artisan('migrate', [
            '--path' => 'database/migrations/2026_08_03_190039_seed_additional_day_ticket_venues.php',
            '--force' => true,
        ])->assertSuccessful();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/2026_08_08_083500_rename_wingate_ponds_to_eden_meadows_fishery.php',
            '--force' => true,
        ])->assertSuccessful();

        $expected = [
            'the-oaks-lakes-sessay' => 'The Oaks Lakes',
            'charltons-pond' => "Charlton's Pond",
            'green-lane-ponds' => 'Green Lane Ponds',
            'renny-lakes' => 'Renny Lakes',
            'woodland-lakes' => 'Woodland Lakes',
            'watergate-lake' => 'Watergate Lake',
            'eden-meadows-fishery' => 'Eden Meadows Fishery',
        ];

        foreach ($expected as $slug => $name) {
            $venue = Venue::query()->where('slug', $slug)->first();

            $this->assertNotNull($venue, "Missing venue {$slug}");
            $this->assertSame($admin->id, $venue->user_id);
            $this->assertTrue($venue->is_approved);
            $this->assertTrue($venue->waters()->exists(), "Venue {$slug} has no waters");

            $this->get(route('venues.show', $venue))
                ->assertOk()
                ->assertSee($name);

            $this->get(route('venues.index', ['q' => $name]))
                ->assertOk()
                ->assertSee($name);
        }

        $eden = Venue::query()->where('slug', 'eden-meadows-fishery')->first();
        $this->assertNotNull($eden);
        $this->assertStringContainsString('Wingate Ponds', (string) $eden->overview);
        $this->assertDatabaseMissing('venues', ['slug' => 'wingate-ponds']);
    }
}
