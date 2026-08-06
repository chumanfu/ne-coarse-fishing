<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\FishingSession;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use App\Support\Uploads;
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
        Storage::fake(Uploads::diskName());

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

        $peg = WaterPeg::query()->where('water_id', $water->id)->where('number', '14')->first();
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
            ->assertSee('14 · Island')
            ->assertSee($peg->fresh()->photos->first()->url(), false);
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
        Storage::fake(Uploads::diskName());
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

        $peg = WaterPeg::query()->where('water_id', $water->id)->where('number', '9')->first();
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

    public function test_edit_session_recalls_saved_water(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create(['name' => 'Match Lake']);
        $peg = WaterPeg::factory()->for($water)->create([
            'number' => '3',
            'name' => 'Dam wall',
            'is_verified' => true,
        ]);

        $session = FishingSession::factory()->for($user)->for($venue)->create([
            'water_id' => $water->id,
            'water_peg_id' => $peg->id,
        ]);

        $this->actingAs($user)
            ->get(route('sessions.edit', $session))
            ->assertOk()
            ->assertSee("waterId: '{$water->id}'", false)
            ->assertSee("waterPegId: '{$peg->id}'", false)
            ->assertSee('Dam wall', false);

        $sessionWithoutWater = FishingSession::factory()->for($user)->for($venue)->create([
            'water_id' => null,
            'water_peg_id' => $peg->id,
        ]);

        $this->actingAs($user)
            ->get(route('sessions.edit', $sessionWithoutWater))
            ->assertOk()
            ->assertSee("waterId: '{$water->id}'", false)
            ->assertSee("waterPegId: '{$peg->id}'", false);
    }

    public function test_edit_session_includes_linked_peg_not_normally_visible(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create();
        $peg = WaterPeg::factory()->for($water)->create([
            'number' => '9',
            'name' => 'Hidden corner',
            'is_verified' => false,
            'created_by' => $other->id,
            'latitude' => 55.03304,
            'longitude' => -1.57723,
        ]);

        $session = FishingSession::factory()->for($owner)->for($venue)->create([
            'water_id' => $water->id,
            'water_peg_id' => $peg->id,
            'peg_latitude' => 55.03304,
            'peg_longitude' => -1.57723,
        ]);

        $this->actingAs($owner)
            ->get(route('sessions.edit', $session))
            ->assertOk()
            ->assertSee("waterPegId: '{$peg->id}'", false)
            ->assertSee('Hidden corner', false);
    }

    public function test_user_can_remove_session_photos_when_editing(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $session = FishingSession::factory()->for($user)->for($venue)->create();

        $disk = Uploads::diskName();
        $keep = $session->photos()->create(['image_path' => 'session-photos/keep.jpg']);
        $remove = $session->photos()->create(['image_path' => 'session-photos/remove.jpg']);
        Storage::disk($disk)->put('session-photos/keep.jpg', 'keep');
        Storage::disk($disk)->put('session-photos/remove.jpg', 'remove');

        $this->actingAs($user)
            ->get(route('sessions.edit', $session))
            ->assertOk()
            ->assertSee('remove_photo_ids[]', false)
            ->assertSee($keep->url(), false);

        $this->actingAs($user)
            ->patch(route('sessions.update', $session), [
                'venue_id' => $venue->id,
                'fished_at' => $session->fished_at->toDateString(),
                'peg_mode' => 'none',
                'remove_photo_ids' => [$remove->id],
            ])
            ->assertRedirect(route('sessions.show', $session));

        $this->assertDatabaseHas('session_photos', ['id' => $keep->id]);
        $this->assertDatabaseMissing('session_photos', ['id' => $remove->id]);
        Storage::disk($disk)->assertExists('session-photos/keep.jpg');
        Storage::disk($disk)->assertMissing('session-photos/remove.jpg');
    }

    public function test_user_cannot_remove_another_sessions_photos(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $other = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $session = FishingSession::factory()->for($user)->for($venue)->create();
        $otherSession = FishingSession::factory()->for($other)->for($venue)->create();
        $foreignPhoto = $otherSession->photos()->create(['image_path' => 'session-photos/foreign.jpg']);

        $this->actingAs($user)
            ->from(route('sessions.edit', $session))
            ->patch(route('sessions.update', $session), [
                'venue_id' => $venue->id,
                'fished_at' => $session->fished_at->toDateString(),
                'peg_mode' => 'none',
                'remove_photo_ids' => [$foreignPhoto->id],
            ])
            ->assertRedirect(route('sessions.edit', $session))
            ->assertSessionHasErrors('remove_photo_ids.0');

        $this->assertDatabaseHas('session_photos', ['id' => $foreignPhoto->id]);
    }
}
