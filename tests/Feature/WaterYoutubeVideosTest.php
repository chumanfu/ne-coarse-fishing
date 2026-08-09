<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Models\WaterVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WaterYoutubeVideosTest extends TestCase
{
    use RefreshDatabase;

    public function test_extracts_youtube_ids_from_common_url_formats(): void
    {
        $this->assertSame('dQw4w9WgXcQ', WaterVideo::extractYoutubeId('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', WaterVideo::extractYoutubeId('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', WaterVideo::extractYoutubeId('https://www.youtube.com/embed/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', WaterVideo::extractYoutubeId('https://www.youtube.com/shorts/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', WaterVideo::extractYoutubeId('dQw4w9WgXcQ'));
        $this->assertNull(WaterVideo::extractYoutubeId('https://example.com/watch?v=dQw4w9WgXcQ'));
    }

    public function test_angler_video_requires_approval_before_public_display(): void
    {
        Role::findOrCreate('fishery_manager');

        $angler = User::factory()->create();
        $manager = User::factory()->create();
        $manager->assignRole('fishery_manager');
        $venue = Venue::factory()->create([
            'is_approved' => true,
            'manager_id' => $manager->id,
        ]);
        $water = Water::factory()->for($venue)->create(['name' => 'Specimen Lake']);

        $this->actingAs($angler)
            ->post(route('waters.videos.store', [$venue, $water]), [
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'title' => 'Lake walkthrough',
            ])
            ->assertRedirect();

        $video = WaterVideo::query()->where('water_id', $water->id)->first();
        $this->assertNotNull($video);
        $this->assertFalse($video->is_approved);
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_id);
        $this->assertSame('Lake walkthrough', $video->title);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('No approved videos yet.')
            ->assertDontSee('Pending video approval')
            ->assertDontSee('youtube-nocookie.com');

        $this->actingAs($manager)
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Pending video approval')
            ->assertSee('Lake walkthrough');

        $this->actingAs($manager)
            ->post(route('waters.videos.approve', [$venue, $water, $video]))
            ->assertRedirect();

        $this->assertTrue($video->fresh()->is_approved);

        auth()->logout();

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertDontSee('No approved videos yet.')
            ->assertSee('Angler videos')
            ->assertSee('dQw4w9WgXcQ')
            ->assertSee('youtube-nocookie.com')
            ->assertSee('waterVideoRow')
            ->assertSee('i.ytimg.com/vi/dQw4w9WgXcQ', false);
    }

    public function test_rejects_invalid_youtube_urls(): void
    {
        $angler = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create();

        $this->actingAs($angler)
            ->from(route('venues.show', $venue))
            ->post(route('waters.videos.store', [$venue, $water]), [
                'youtube_url' => 'https://vimeo.com/123456',
            ])
            ->assertRedirect(route('venues.show', $venue))
            ->assertSessionHasErrors('youtube_url');

        $this->assertDatabaseCount('water_videos', 0);
    }

    public function test_video_row_shows_multiple_thumbnails(): void
    {
        Role::findOrCreate('fishery_manager');

        $manager = User::factory()->create();
        $manager->assignRole('fishery_manager');
        $venue = Venue::factory()->create([
            'is_approved' => true,
            'manager_id' => $manager->id,
        ]);
        $water = Water::factory()->for($venue)->create();

        foreach (['aaaaaaaaaaa', 'bbbbbbbbbbb', 'ccccccccccc'] as $i => $id) {
            $water->videos()->create([
                'user_id' => $manager->id,
                'youtube_url' => 'https://youtu.be/'.$id,
                'youtube_id' => $id,
                'title' => 'Clip '.($i + 1),
                'is_approved' => true,
                'approved_by' => $manager->id,
                'approved_at' => now(),
                'sort_order' => $i + 1,
            ]);
        }

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Clip 1')
            ->assertSee('Clip 2')
            ->assertSee('Clip 3')
            ->assertSee('aaaaaaaaaaa')
            ->assertSee('bbbbbbbbbbb')
            ->assertSee('Scroll videos left')
            ->assertSee('Scroll videos right')
            ->assertSee('Play Clip 1');
    }
}
