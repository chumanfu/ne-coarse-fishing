<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\FishingSession;
use App\Models\SessionPhoto;
use App\Models\TackleShop;
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
        Storage::disk(Uploads::diskName())->put('session-photos/catch.jpg', 'fake');

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $session = FishingSession::factory()->for($user)->for($venue)->create();
        SessionPhoto::query()->create([
            'fishing_session_id' => $session->id,
            'image_path' => 'session-photos/bank.jpg',
        ]);
        SessionPhoto::query()->create([
            'fishing_session_id' => $session->id,
            'image_path' => 'session-photos/catch.jpg',
        ]);

        $response = $this->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Session photo')
            ->assertSee('<div x-data class="grid grid-cols-2 sm:grid-cols-3 gap-3">', false)
            ->assertSee('session-photos/bank.jpg')
            ->assertSee('session-photos/catch.jpg')
            ->assertSee('$store.photoLightbox.open', false);

        $this->assertSame(
            2,
            substr_count($response->getContent(), '@click="$store.photoLightbox.open'),
            'Each session photo should open its position in the shared gallery.',
        );
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

    public function test_about_page_content_photos_are_clickable_for_lightbox(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('canal-fishing-vintage.png')
            ->assertSee('chris-bankside-catch.png')
            ->assertSee('$store.photoLightbox.open', false)
            ->assertSee('About photo');
    }

    public function test_directory_logos_are_clickable_for_lightbox(): void
    {
        Storage::fake(Uploads::diskName());

        $club = Club::factory()->create([
            'name' => 'Lightbox Angling Club',
            'logo_path' => 'club-logos/lightbox-club.png',
        ]);
        $shop = TackleShop::factory()->create([
            'name' => 'Lightbox Tackle Shop',
            'logo_path' => 'tackle-shop-logos/lightbox-shop.png',
        ]);

        $this->get(route('clubs.show', $club))
            ->assertOk()
            ->assertSee('$store.photoLightbox.open', false)
            ->assertSee('Lightbox Angling Club logo');

        $this->get(route('tackle-shops.show', $shop))
            ->assertOk()
            ->assertSee('$store.photoLightbox.open', false)
            ->assertSee('Lightbox Tackle Shop logo');

        $this->get(route('clubs.index'))
            ->assertOk()
            ->assertSee('$store.photoLightbox.open', false);

        $this->get(route('tackle-shops.index'))
            ->assertOk()
            ->assertSee('$store.photoLightbox.open', false);
    }

    public function test_photo_directory_thumbnails_are_clickable_for_lightbox(): void
    {
        Storage::fake(Uploads::diskName());

        $venue = Venue::factory()->create([
            'name' => 'Directory Photo Lake',
            'is_approved' => true,
        ]);
        VenuePhoto::query()->create([
            'venue_id' => $venue->id,
            'image_path' => 'venue-photos/directory-lake.jpg',
            'sort_order' => 1,
        ]);

        $review = TackleReview::factory()->for(User::factory())->create([
            'title' => 'Directory Photo Rod',
            'is_published' => true,
        ]);
        TackleReviewPhoto::query()->create([
            'tackle_review_id' => $review->id,
            'image_path' => 'review-photos/directory-rod.jpg',
            'sort_order' => 1,
        ]);

        $this->get(route('venues.index', ['q' => 'Directory Photo Lake']))
            ->assertOk()
            ->assertSee('Directory Photo Lake photo')
            ->assertSee('@click.prevent.stop="$store.photoLightbox.open', false);

        $this->get(route('tackle-reviews.index'))
            ->assertOk()
            ->assertSee('Directory Photo Rod photo')
            ->assertSee('@click.prevent.stop="$store.photoLightbox.open', false);
    }

    public function test_opened_photo_is_contained_in_its_frame_at_its_own_aspect_ratio(): void
    {
        $markup = $this->get(route('venues.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('photo-lightbox__frame', $markup);
        $this->assertStringContainsString('photo-lightbox__image', $markup);
        $this->assertStringContainsString("{ '--photo-zoom': \$store.photoLightbox.scale }", $markup);

        // The frame must own the space left over below the toolbar, otherwise the image has
        // no definite height to be contained within.
        $this->assertStringContainsString('min-h-0 flex-1 overflow-auto', $markup);

        // A CSS transform does not grow the scroll area, so zooming used to leave the hidden
        // edges of the photo unreachable.
        $this->assertStringNotContainsString('transform: scale(${$store.photoLightbox.scale})', $markup);

        $css = file_get_contents(resource_path('css/app.css'));

        preg_match('/\.photo-lightbox__image\s*\{(.*?)\}/s', $css, $rule);
        $this->assertNotEmpty($rule, 'Expected a .photo-lightbox__image rule in app.css');

        // `flex: none` plus auto margins stop the frame stretching the <img> box, which is
        // what squashed portrait photos out of aspect ratio.
        $this->assertStringContainsString('flex: none', $rule[1]);
        $this->assertStringContainsString('margin: auto', $rule[1]);
        $this->assertStringContainsString('object-fit: contain', $rule[1]);
        $this->assertStringContainsString('max-width: calc(var(--photo-zoom) * 100%)', $rule[1]);
        $this->assertStringContainsString('max-height: calc(var(--photo-zoom) * 100%)', $rule[1]);
    }

    public function test_lightbox_overlay_sits_above_leaflet_map_layers(): void
    {
        $markup = file_get_contents(resource_path('views/components/photo-lightbox.blade.php'));

        preg_match('/class="fixed inset-0 z-\[(\d+)\]/', $markup, $overlay);
        $this->assertNotEmpty($overlay, 'Expected a z-index on the lightbox overlay');

        // Leaflet stacks its controls up to z-index 1000 and its map container makes no
        // stacking context of its own, so a lower overlay is drawn under the map.
        $this->assertGreaterThan(1000, (int) $overlay[1]);
    }

    public function test_pond_map_images_do_not_open_the_photo_lightbox(): void
    {
        $mapImages = [
            [resource_path('views/components/pond-map.blade.php'), 'src="{{ $src }}"'],
            [resource_path('views/components/pond-map-placer.blade.php'), 'src="{{ $src }}"'],
            [resource_path('views/components/pond-map-explorer.blade.php'), 'src="{{ $src }}"'],
            [resource_path('views/pegs/form.blade.php'), ':src="selectedWater?.map_url"'],
            [resource_path('views/sessions/create.blade.php'), ':src="selectedWaterMapUrl"'],
            [resource_path('views/livewire/venue-wizard.blade.php'), 'src="{{ $water[\'map_image_url\'] }}"'],
            [resource_path('views/livewire/venue-wizard.blade.php'), 'alt="Pond map"'],
        ];

        foreach ($mapImages as [$path, $marker]) {
            $source = file_get_contents($path);
            preg_match_all('/<img\b[^>]*>/s', $source, $matches);
            $mapTag = collect($matches[0])->first(
                fn (string $tag): bool => str_contains($tag, $marker)
            );

            $this->assertNotNull($mapTag, "Expected a map image in {$path}");
            $this->assertStringNotContainsString('photoLightbox', $mapTag);
            $this->assertStringNotContainsString('cursor-zoom-in', $mapTag);
        }
    }
}
