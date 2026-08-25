<?php

namespace Tests\Feature;

use App\Models\Venue;
use App\Models\VenuePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VenueStockPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_venues_have_stock_photos_linked(): void
    {
        $this->assertTrue(File::exists(public_path('images/venues/killingworth-lakes.jpg')));

        $venue = Venue::query()->where('slug', 'killingworth-lakes')->first();
        $this->assertNotNull($venue);

        $photo = $venue->photos()->first();
        $this->assertNotNull($photo);
        $this->assertSame('images/venues/killingworth-lakes.jpg', $photo->image_path);
        $this->assertStringContainsString('/images/venues/killingworth-lakes.jpg', $photo->url());
    }

    public function test_venue_index_shows_stock_photo(): void
    {
        $venue = Venue::factory()->create(['is_approved' => true, 'name' => 'Photo Lake']);
        VenuePhoto::query()->create([
            'venue_id' => $venue->id,
            'image_path' => 'images/venues/bolam-lake.jpg',
            'sort_order' => 0,
        ]);

        $this->get(route('venues.index'))
            ->assertOk()
            ->assertSee('images/venues/bolam-lake.jpg', false);
    }

    public function test_home_shows_featured_venue_photos(): void
    {
        $venue = Venue::factory()->create(['is_approved' => true, 'name' => 'Home Photo Lake']);
        VenuePhoto::query()->create([
            'venue_id' => $venue->id,
            'image_path' => 'images/venues/angel-lakes.jpg',
            'sort_order' => 0,
        ]);

        Venue::factory()->create([
            'is_approved' => true,
            'name' => 'Home No Photo Lake',
            'created_at' => now()->addMinute(),
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $venuesStart = strpos($html, 'home-section--venues');
        $venuesEnd = strpos($html, 'home-section--clubs');
        $this->assertNotFalse($venuesStart);
        $this->assertNotFalse($venuesEnd);
        $venuesSection = substr($html, $venuesStart, $venuesEnd - $venuesStart);

        $this->assertStringContainsString('images/venues/angel-lakes.jpg', $venuesSection);
        $this->assertStringContainsString('Home Photo Lake', $venuesSection);
        $this->assertStringNotContainsString('Home No Photo Lake', $venuesSection);
        $this->assertStringNotContainsString('Photo coming soon', $venuesSection);
    }
}
