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

    public function test_home_shows_only_five_latest_activities_with_view_all_link(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);

        foreach (range(1, 7) as $i) {
            Activity::query()->create([
                'type' => Activity::TYPE_SESSION,
                'subject_type' => Venue::class,
                'subject_id' => $venue->id,
                'user_id' => $user->id,
                'title' => "Activity item {$i}",
                'summary' => "Summary {$i}",
                'url' => '/venues/'.$venue->slug,
                'created_at' => now()->subMinutes(10 - $i),
                'updated_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Latest activity')
            ->assertSee('View all')
            ->assertSee(route('activity.index', absolute: false), false)
            ->assertSee('Activity item 7')
            ->assertSee('Activity item 3')
            ->assertDontSee('Activity item 2')
            ->assertDontSee('Activity item 1');
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
}
