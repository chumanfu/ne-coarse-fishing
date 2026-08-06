<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\TackleShop;
use App\Models\Venue;
use App\Models\VenuePhoto;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MigrateStockImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_images_are_promoted_onto_uploads_disk(): void
    {
        Storage::fake(Uploads::diskName());

        $relative = 'images/venues/test-stock.jpg';
        $absolute = public_path($relative);
        File::ensureDirectoryExists(dirname($absolute));
        File::put($absolute, 'fake-image-bytes');

        $venue = Venue::factory()->create(['is_approved' => true]);
        $photo = VenuePhoto::query()->create([
            'venue_id' => $venue->id,
            'image_path' => $relative,
            'sort_order' => 0,
        ]);

        $club = Club::factory()->create(['logo_path' => 'images/clubs/test-club.png']);
        File::ensureDirectoryExists(public_path('images/clubs'));
        File::put(public_path('images/clubs/test-club.png'), 'club-logo');

        $shop = TackleShop::factory()->create(['logo_path' => 'images/tackle-shops/test-shop.png']);
        File::ensureDirectoryExists(public_path('images/tackle-shops'));
        File::put(public_path('images/tackle-shops/test-shop.png'), 'shop-logo');

        $this->artisan('uploads:migrate-stock-images')
            ->assertSuccessful();

        $this->assertSame('venue-photos/stock/venues/test-stock.jpg', $photo->fresh()->image_path);
        $this->assertSame('club-logos/stock/clubs/test-club.png', $club->fresh()->logo_path);
        $this->assertSame('tackle-shop-logos/stock/tackle-shops/test-shop.png', $shop->fresh()->logo_path);

        Storage::disk(Uploads::diskName())->assertExists('venue-photos/stock/venues/test-stock.jpg');
        Storage::disk(Uploads::diskName())->assertExists('club-logos/stock/clubs/test-club.png');
        Storage::disk(Uploads::diskName())->assertExists('tackle-shop-logos/stock/tackle-shops/test-shop.png');

        File::delete($absolute);
        File::delete(public_path('images/clubs/test-club.png'));
        File::delete(public_path('images/tackle-shops/test-shop.png'));
    }
}
