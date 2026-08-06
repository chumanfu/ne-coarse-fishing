<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavouriteVenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_favourite_a_venue(): void
    {
        $venue = Venue::factory()->create(['is_approved' => true]);

        $this->post(route('venues.favourite.store', $venue))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_favourite_and_unfavourite_a_venue(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true, 'name' => 'Killingworth Lakes']);

        $this->actingAs($user)
            ->from(route('venues.show', $venue))
            ->post(route('venues.favourite.store', $venue))
            ->assertRedirect(route('venues.show', $venue));

        $this->assertTrue($user->fresh()->hasFavourited($venue));

        $this->actingAs($user)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('★ Favourited', false);

        $this->actingAs($user)
            ->get(route('venues.favourites'))
            ->assertOk()
            ->assertSee('Killingworth Lakes');

        $this->actingAs($user)
            ->from(route('venues.show', $venue))
            ->delete(route('venues.favourite.destroy', $venue))
            ->assertRedirect(route('venues.show', $venue));

        $this->assertFalse($user->fresh()->hasFavourited($venue));
    }

    public function test_favouriting_twice_is_idempotent(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);

        $this->actingAs($user)->post(route('venues.favourite.store', $venue));
        $this->actingAs($user)->post(route('venues.favourite.store', $venue));

        $this->assertSame(1, $user->favouriteVenues()->count());
    }
}
