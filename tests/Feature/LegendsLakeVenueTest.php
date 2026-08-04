<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterPeg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegendsLakeVenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_show_renders_peg_descriptions_and_public_images(): void
    {
        $user = User::factory()->create();

        $venue = Venue::factory()->create([
            'user_id' => $user->id,
            'name' => 'Legends Lake',
            'slug' => 'legends-lake-test',
            'is_approved' => true,
            'url' => 'https://legendslake.com/',
        ]);

        $water = Water::factory()->for($venue)->create(['name' => 'Legends Lake']);

        $peg = WaterPeg::factory()->for($water)->create([
            'created_by' => $user->id,
            'number' => '1',
            'name' => 'The Bounty',
            'description' => 'Peg closest to the car park with deep water to 14ft.',
            'is_verified' => true,
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        $peg->photos()->create([
            'image_path' => 'images/venues/legends-lake/pegs/1-the-bounty-1.jpg',
            'sort_order' => 0,
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Pegs')
            ->assertSee('The Bounty')
            ->assertSee('Peg closest to the car park with deep water to 14ft.')
            ->assertSee('images/venues/legends-lake/pegs/1-the-bounty-1.jpg')
            ->assertSee('https://legendslake.com/');
    }
}
