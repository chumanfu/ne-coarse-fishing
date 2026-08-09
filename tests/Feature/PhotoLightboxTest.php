<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\SessionPhoto;
use App\Models\TackleReview;
use App\Models\TackleReviewPhoto;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePhoto;
use App\Models\Water;
use App\Models\WaterPhoto;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoLightboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_layout_includes_shared_photo_lightbox(): void
    {
        $this->get(route('venues.index'))
            ->assertOk()
            ->assertSee('photoLightbox', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('Close photo', false);
    }

    public function test_venue_photos_open_via_lightbox_gallery(): void
    {
        Storage::fake(Uploads::diskName());
        Storage::disk(Uploads::diskName())->put('venue-photos/lake.jpg', 'fake');

        $venue = Venue::factory()->create([
            'name' => 'Lightbox Lakes',
            'is_approved' => true,
        ]);
        VenuePhoto::query()->create([
            'venue_id' => $venue->id,
            'image_path' => 'venue-photos/lake.jpg',
            'sort_order' => 1,
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('$store.photoLightbox.open', false)
            ->assertSee('Lightbox Lakes photo')
            ->assertSee('Venue photo');
    }

    public function test_water_photos_use_shared_lightbox_not_page_local_one(): void
    {
        Storage::fake(Uploads::diskName());
        Storage::disk(Uploads::diskName())->put('water-photos/catch.jpg', 'fake');

        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create(['name' => 'Match Lake']);
        WaterPhoto::query()->create([
            'water_id' => $water->id,
            'user_id' => User::factory()->create()->id,
            'image_path' => 'water-photos/catch.jpg',
            'is_approved' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Angler photos')
            ->assertSee('$store.photoLightbox.open', false)
            ->assertDontSee('waterPhotoLightbox', false);
    }

    public function test_session_photos_are_clickable_for_lightbox(): void
    {
        Storage::fake(Uploads::diskName());
        Storage::disk(Uploads::diskName())->put('session-photos/bank.jpg', 'fake');

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $session = FishingSession::factory()->for($user)->for($venue)->create();
        SessionPhoto::query()->create([
            'fishing_session_id' => $session->id,
            'image_path' => 'session-photos/bank.jpg',
        ]);

        $this->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Session photo')
            ->assertSee('$store.photoLightbox.open', false);
    }

    public function test_tackle_review_photos_are_clickable_for_lightbox(): void
    {
        Storage::fake(Uploads::diskName());
        Storage::disk(Uploads::diskName())->put('review-photos/rod.jpg', 'fake');

        $user = User::factory()->create();
        $review = TackleReview::factory()->for($user)->create([
            'title' => 'Feeder Rod',
            'is_published' => true,
        ]);
        TackleReviewPhoto::query()->create([
            'tackle_review_id' => $review->id,
            'image_path' => 'review-photos/rod.jpg',
            'sort_order' => 1,
        ]);

        $this->get(route('tackle-reviews.show', $review))
            ->assertOk()
            ->assertSee('$store.photoLightbox.open', false)
            ->assertSee('Review photo');
    }
}
