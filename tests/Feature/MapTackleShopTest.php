<?php

namespace Tests\Feature;

use App\Models\TackleShop;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapTackleShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_page_includes_red_tackle_shop_markers(): void
    {
        Venue::factory()->create([
            'name' => 'Map Lake',
            'is_approved' => true,
            'latitude' => 55.01,
            'longitude' => -1.55,
        ]);

        TackleShop::factory()->create([
            'name' => 'Mapped Tackle Shop',
            'slug' => 'mapped-tackle-shop',
            'latitude' => 54.99,
            'longitude' => -1.53,
            'is_published' => true,
        ]);

        TackleShop::factory()->create([
            'name' => 'Online Only Shop',
            'latitude' => null,
            'longitude' => null,
            'location_type' => 'online',
            'is_published' => true,
        ]);

        $this->get(route('map.index'))
            ->assertOk()
            ->assertSee('Mapped Tackle Shop')
            ->assertSee('map-pin--shop', false)
            ->assertSee('Tackle shops')
            ->assertDontSee('Online Only Shop');
    }

    public function test_home_map_includes_tackle_shops(): void
    {
        Venue::factory()->create([
            'name' => 'Home Map Mere',
            'is_approved' => true,
            'latitude' => 55.01,
            'longitude' => -1.55,
        ]);

        TackleShop::factory()->create([
            'name' => 'Home Map Tackle',
            'latitude' => 54.98,
            'longitude' => -1.52,
            'is_published' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Home Map Tackle')
            ->assertSee('map-pin--shop', false)
            ->assertSee('View shop', false);
    }

    public function test_venue_filters_hide_tackle_shops_on_map(): void
    {
        Venue::factory()->create([
            'name' => 'Filtered Lake',
            'is_approved' => true,
            'ticket_type' => 'day_ticket',
            'latitude' => 55.01,
            'longitude' => -1.55,
        ]);

        TackleShop::factory()->create([
            'name' => 'Hidden When Filtered',
            'latitude' => 54.99,
            'longitude' => -1.53,
            'is_published' => true,
        ]);

        $this->get(route('map.index', ['ticket_type' => 'day_ticket']))
            ->assertOk()
            ->assertSee('Filtered Lake')
            ->assertDontSee('Hidden When Filtered');
    }
}
