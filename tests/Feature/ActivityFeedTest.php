<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_only_ten_latest_activities_with_view_all_link(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);

        foreach (range(1, 12) as $i) {
            Activity::query()->create([
                'type' => Activity::TYPE_SESSION,
                'subject_type' => Venue::class,
                'subject_id' => $venue->id,
                'user_id' => $user->id,
                'title' => sprintf('Activity item %02d', $i),
                'summary' => "Summary {$i}",
                'url' => '/venues/'.$venue->slug,
                'created_at' => now()->subMinutes(20 - $i),
                'updated_at' => now()->subMinutes(20 - $i),
            ]);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Latest activity')
            ->assertSee('View all')
            ->assertSee(route('activity.index', absolute: false), false)
            ->assertSee('Activity item 12')
            ->assertSee('Activity item 03')
            ->assertDontSee('Activity item 02')
            ->assertDontSee('Activity item 01');
    }

    public function test_activity_index_lists_all_items(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);

        foreach (range(1, 6) as $i) {
            Activity::query()->create([
                'type' => Activity::TYPE_VENUE,
                'subject_type' => Venue::class,
                'subject_id' => $venue->id,
                'user_id' => $user->id,
                'title' => "Full feed item {$i}",
                'summary' => null,
                'url' => '/venues/'.$venue->slug,
            ]);
        }

        $this->get(route('activity.index'))
            ->assertOk()
            ->assertSee('Activity')
            ->assertSee('Full feed item 1')
            ->assertSee('Full feed item 6');
    }

    public function test_public_activity_shows_allowed_entity_activities(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);

        $allowed = [
            [Activity::TYPE_VENUE, 'Public venue activity'],
            [Activity::TYPE_PEG, 'Public peg activity'],
            [Activity::TYPE_CLUB, 'Public club activity'],
            [Activity::TYPE_TACKLE_SHOP, 'Public tackle shop activity'],
            [Activity::TYPE_SESSION, 'Public session activity'],
        ];

        foreach ($allowed as [$type, $title]) {
            Activity::query()->create([
                'type' => $type,
                'subject_type' => Venue::class,
                'subject_id' => $venue->id,
                'user_id' => $user->id,
                'title' => $title,
                'summary' => null,
                'url' => '/venues/'.$venue->slug,
            ]);
        }

        $response = $this->get(route('activity.index'))->assertOk();

        foreach ($allowed as [, $title]) {
            $response->assertSee($title);
        }
    }

    public function test_public_activity_hides_disallowed_activity_types(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);

        $disallowed = [
            [Activity::TYPE_USER_REGISTERED, $user->name.' joined NE Coarse Fishing'],
            [Activity::TYPE_VENUE_SUBMITTED, 'Venue submitted for review'],
            [Activity::TYPE_TACTIC, 'Shared tactic activity'],
            [Activity::TYPE_VENUE_CLAIM, 'Venue claim activity'],
            [Activity::TYPE_VENUE_EDIT_REQUEST, 'Venue edit suggestion activity'],
            [Activity::TYPE_CLUB_CLAIM, 'Club claim activity'],
            [Activity::TYPE_CLUB_EDIT_REQUEST, 'Club edit suggestion activity'],
            [Activity::TYPE_SHOP_CLAIM, 'Shop claim activity'],
            [Activity::TYPE_SHOP_EDIT_REQUEST, 'Shop edit suggestion activity'],
            [Activity::TYPE_MATCH_REPORT, 'Match report activity'],
            [Activity::TYPE_ANNOUNCEMENT, 'Announcement activity'],
            [Activity::TYPE_TACKLE_REVIEW, 'Tackle review activity'],
            [Activity::TYPE_MESSAGE, 'New message activity'],
        ];

        foreach ($disallowed as [$type, $title]) {
            Activity::query()->create([
                'type' => $type,
                'subject_type' => Venue::class,
                'subject_id' => $venue->id,
                'user_id' => $user->id,
                'title' => $title,
                'summary' => null,
                'url' => '/venues/'.$venue->slug,
            ]);
        }

        Activity::query()->create([
            'type' => Activity::TYPE_SESSION,
            'subject_type' => Venue::class,
            'subject_id' => $venue->id,
            'user_id' => $user->id,
            'title' => 'Allowed session activity',
            'summary' => null,
            'url' => '/venues/'.$venue->slug,
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Allowed session activity');

        foreach ($disallowed as [, $title]) {
            $this->get(route('home'))->assertDontSee($title);
        }

        $this->get(route('activity.index'))
            ->assertOk()
            ->assertSee('Allowed session activity');

        foreach ($disallowed as [, $title]) {
            $this->get(route('activity.index'))->assertDontSee($title);
        }
    }
}
