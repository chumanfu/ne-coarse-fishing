<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BirtleyRofAnglingClubTest extends TestCase
{
    use RefreshDatabase;

    public function test_birtley_rof_club_and_ouston_springs_are_seeded(): void
    {
        $admin = User::query()->where('email', 'admin@nefishing.test')->first()
            ?? User::factory()->create(['email' => 'admin@nefishing.test']);

        $this->artisan('migrate', [
            '--path' => 'database/migrations/2026_08_08_190500_seed_birtley_rof_angling_club.php',
            '--force' => true,
        ])->assertSuccessful();

        $club = Club::query()->where('slug', 'birtley-rof-angling-club')->first();
        $venue = Venue::query()->where('slug', 'ouston-springs-pond')->first();

        $this->assertNotNull($club);
        $this->assertSame('Birtley ROF Angling Club', $club->name);
        $this->assertSame('https://www.birtleyrofanglingclub.com/', $club->url);
        $this->assertSame('Birtley', $club->town);
        $this->assertTrue($club->is_published);

        $this->assertNotNull($venue);
        $this->assertSame('Ouston Springs Pond', $venue->name);
        $this->assertSame($admin->id, $venue->user_id);
        $this->assertTrue($venue->is_approved);
        $this->assertTrue($venue->waters()->where('name', 'Ouston Springs Pond')->exists());
        $this->assertTrue($club->venues()->where('slug', 'ouston-springs-pond')->exists());

        $this->get(route('clubs.show', $club))
            ->assertOk()
            ->assertSee('Birtley ROF Angling Club')
            ->assertSee('Ouston Springs Pond');

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Ouston Springs Pond')
            ->assertSee('Birtley ROF', false);

        $this->get(route('clubs.index', ['q' => 'Birtley']))
            ->assertOk()
            ->assertSee('Birtley ROF Angling Club');
    }
}
