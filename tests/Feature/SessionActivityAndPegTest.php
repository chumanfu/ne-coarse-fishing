<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SessionActivityAndPegTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_sessions_link_to_public_session_show(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $session = FishingSession::factory()->for($owner)->for($venue)->create([
            'commentary' => 'Great day on the method.',
        ]);

        $this->actingAs($viewer)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee(route('sessions.show', $session), false);

        $this->actingAs($viewer)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Great day on the method.')
            ->assertDontSee('Edit');

        $this->actingAs($owner)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Edit');
    }

    public function test_logging_session_creates_activity_item(): void
    {
        $user = User::factory()->create(['name' => 'Chris']);
        $venue = Venue::factory()->create(['name' => 'Test Lakes', 'is_approved' => true]);

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'fished_at' => now()->toDateString(),
            'peg_mode' => 'none',
        ])->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'type' => Activity::TYPE_SESSION,
            'title' => 'Chris logged a session at Test Lakes',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Latest activity')
            ->assertSee('Chris logged a session at Test Lakes');
    }

    public function test_new_peg_from_session_is_pending_for_non_managers(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true, 'manager_id' => null]);
        $water = Water::factory()->for($venue)->create(['name' => 'Main Lake']);

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => $water->id,
            'fished_at' => now()->toDateString(),
            'peg_mode' => 'new',
            'peg_number' => '14',
            'peg_name' => 'Island',
            'peg_latitude' => 55.0318,
            'peg_longitude' => -1.5701,
            'peg_photos' => [
                UploadedFile::fake()->image('peg-a.jpg'),
                UploadedFile::fake()->image('peg-b.jpg'),
            ],
        ])->assertRedirect();

        $peg = WaterPeg::query()->first();
        $this->assertNotNull($peg);
        $this->assertFalse($peg->is_verified);
        $this->assertSame($user->id, $peg->created_by);
        $this->assertCount(2, $peg->photos);

        $other = User::factory()->create();
        $visibleToCreator = WaterPeg::query()->visibleTo($user)->whereKey($peg->id)->exists();
        $visibleToOther = WaterPeg::query()->visibleTo($other)->whereKey($peg->id)->exists();
        $this->assertTrue($visibleToCreator);
        $this->assertFalse($visibleToOther);

        $manager = User::factory()->create();
        $venue->update(['manager_id' => $manager->id]);

        $this->actingAs($manager)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Manage pegs')
            ->assertSee('Awaiting verification')
            ->assertSee('Island');

        $this->actingAs($manager)
            ->post(route('pegs.verify', [$venue, $peg]))
            ->assertRedirect();

        $this->assertTrue($peg->fresh()->is_verified);
        $this->assertCount(2, $peg->fresh()->photos);

        $this->actingAs($other)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Peg photos')
            ->assertSee('14 · Island');
    }

    public function test_manager_can_verify_pending_peg(): void
    {
        Role::findOrCreate('fishery_manager');
        $manager = User::factory()->create();
        $manager->assignRole('fishery_manager');
        $venue = Venue::factory()->create(['is_approved' => true, 'manager_id' => $manager->id]);
        $water = Water::factory()->for($venue)->create();
        $peg = WaterPeg::factory()->pending()->for($water)->create();

        $this->actingAs($manager)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Manage pegs')
            ->assertSee('Add peg');

        $this->actingAs($manager)
            ->post(route('pegs.verify', [$venue, $peg]))
            ->assertRedirect();

        $this->assertTrue($peg->fresh()->is_verified);
    }

    public function test_fishery_manager_can_add_official_peg(): void
    {
        Storage::fake('public');
        Role::findOrCreate('fishery_manager');
        $manager = User::factory()->create();
        $manager->assignRole('fishery_manager');
        $venue = Venue::factory()->create([
            'is_approved' => true,
            'manager_id' => $manager->id,
            'latitude' => 54.79,
            'longitude' => -1.62,
        ]);
        $water = Water::factory()->for($venue)->create(['name' => 'Match Lake']);

        $this->actingAs($manager)
            ->get(route('pegs.create', $venue))
            ->assertOk()
            ->assertSee('Add peg');

        $this->actingAs($manager)
            ->post(route('pegs.store', $venue), [
                'water_id' => $water->id,
                'number' => '9',
                'name' => 'Boilie Point',
                'latitude' => 54.791,
                'longitude' => -1.621,
                'photos' => [UploadedFile::fake()->image('peg.jpg')],
            ])
            ->assertRedirect(route('venues.show', $venue));

        $peg = WaterPeg::query()->first();
        $this->assertNotNull($peg);
        $this->assertTrue($peg->is_verified);
        $this->assertSame('9', $peg->number);
        $this->assertSame('Boilie Point', $peg->name);
        $this->assertCount(1, $peg->photos);
    }

    public function test_angler_cannot_add_official_peg(): void
    {
        Role::findOrCreate('angler');
        $angler = User::factory()->create();
        $angler->assignRole('angler');
        $venue = Venue::factory()->create(['is_approved' => true, 'manager_id' => null]);
        $water = Water::factory()->for($venue)->create();

        $this->actingAs($angler)
            ->get(route('pegs.create', $venue))
            ->assertForbidden();

        $this->actingAs($angler)
            ->post(route('pegs.store', $venue), [
                'water_id' => $water->id,
                'number' => '1',
                'latitude' => 54.7,
                'longitude' => -1.5,
            ])
            ->assertForbidden();
    }

    public function test_session_can_use_existing_verified_peg(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create();
        $peg = WaterPeg::factory()->for($water)->create([
            'number' => '7',
            'name' => 'Point',
            'is_verified' => true,
            'latitude' => 55.1,
            'longitude' => -1.6,
        ]);

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => $water->id,
            'water_peg_id' => $peg->id,
            'fished_at' => now()->toDateString(),
            'peg_mode' => 'existing',
        ])->assertRedirect();

        $session = FishingSession::query()->first();
        $this->assertSame($peg->id, $session->water_peg_id);
        $this->assertSame('7 · Point', $session->peg_number);
    }
}
