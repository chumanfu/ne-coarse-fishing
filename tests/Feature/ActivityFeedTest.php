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

    public function test_public_activity_hides_user_signups(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);

        Activity::query()->create([
            'type' => Activity::TYPE_USER_REGISTERED,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'user_id' => $user->id,
            'title' => $user->name.' joined NE Coarse Fishing',
            'summary' => $user->email,
            'url' => '/admin/users/'.$user->id.'/edit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Activity::query()->create([
            'type' => Activity::TYPE_SESSION,
            'subject_type' => Venue::class,
            'subject_id' => $venue->id,
            'user_id' => $user->id,
            'title' => 'Public session activity',
            'summary' => null,
            'url' => '/venues/'.$venue->slug,
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Public session activity')
            ->assertDontSee($user->name.' joined NE Coarse Fishing');

        $this->get(route('activity.index'))
            ->assertOk()
            ->assertSee('Public session activity')
            ->assertDontSee($user->name.' joined NE Coarse Fishing');
    }
}
