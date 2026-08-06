<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\FishingSession;
use App\Models\SiteAnnouncement;
use App\Models\TackleReview;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommunityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_and_refer_pages_are_public(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('NE Coarse Fishing', false)
            ->assertSee('Tight lines', false);

        $this->get(route('refer'))
            ->assertOk()
            ->assertSee('WhatsApp', false)
            ->assertSee('Facebook', false)
            ->assertSee('Instagram', false)
            ->assertSee('Email', false);
    }

    public function test_venue_and_session_show_share_and_facebook_links(): void
    {
        $venue = Venue::factory()->create([
            'is_approved' => true,
            'facebook_url' => 'https://facebook.com/examplevenue',
            'url' => 'https://example.com/venue',
        ]);

        $session = FishingSession::factory()->create([
            'venue_id' => $venue->id,
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Share venue', false)
            ->assertSee('WhatsApp', false)
            ->assertSee('https://facebook.com/examplevenue', false)
            ->assertSee('Visit website', false);

        $this->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Share session', false)
            ->assertSee('Instagram', false);
    }

    public function test_site_announcement_banner_respects_display_window(): void
    {
        SiteAnnouncement::factory()->create([
            'title' => 'Maintenance tonight',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHours(2),
            'is_active' => true,
        ]);

        SiteAnnouncement::factory()->expired()->create([
            'title' => 'Old notice',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Maintenance tonight', false)
            ->assertDontSee('Old notice', false);
    }

    public function test_venue_announcement_scheduling_hides_expired(): void
    {
        $venue = Venue::factory()->create(['is_approved' => true]);

        Announcement::factory()->create([
            'venue_id' => $venue->id,
            'title' => 'Visible stocking',
            'published_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
        ]);

        Announcement::factory()->expired()->create([
            'venue_id' => $venue->id,
            'title' => 'Expired stocking',
        ]);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Visible stocking', false)
            ->assertDontSee('Expired stocking', false);
    }

    public function test_angler_can_create_tackle_review_with_zero_stars(): void
    {
        Role::findOrCreate('angler');
        $user = User::factory()->create();
        $user->assignRole('angler');

        $this->actingAs($user)
            ->post(route('tackle-reviews.store'), [
                'title' => 'Budget feeder rod',
                'brand' => 'Daiwa',
                'rating' => 0,
                'body' => 'Not for me, but others might like it.',
                'purchase_url' => 'https://example.com/rod',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tackle_reviews', [
            'user_id' => $user->id,
            'title' => 'Budget feeder rod',
            'rating' => 0,
            'is_published' => true,
        ]);

        $review = TackleReview::query()->first();

        $this->get(route('tackle-reviews.show', $review))
            ->assertOk()
            ->assertSee('Budget feeder rod', false)
            ->assertSee('0/5', false);
    }

    public function test_featured_tackle_review_appears_on_home(): void
    {
        TackleReview::factory()->featured()->create([
            'title' => 'Featured reel',
            'body' => 'A solid all-rounder for the Tyne.',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tackle reviews', false)
            ->assertSee('Featured reel', false);
    }

    public function test_tackle_review_create_route_is_not_captured_by_show(): void
    {
        Role::findOrCreate('angler');
        $user = User::factory()->create();
        $user->assignRole('angler');

        $this->actingAs($user)
            ->get(route('tackle-reviews.create'))
            ->assertOk();
    }
}
