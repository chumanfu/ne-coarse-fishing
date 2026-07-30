<?php

namespace Tests\Feature;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VenuePhotoAndWhat3wordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_normalizes_what3words(): void
    {
        $this->assertSame('filled.count.soap', Venue::normalizeWhat3words('///Filled.Count.Soap'));
        $this->assertNull(Venue::normalizeWhat3words(''));
    }

    public function test_venue_show_displays_what3words_and_photos(): void
    {
        Storage::fake('public');

        $venue = Venue::factory()->create([
            'what3words' => 'filled.count.soap',
            'is_approved' => true,
        ]);

        $venue->photos()->create([
            'image_path' => 'venue-photos/test.jpg',
            'sort_order' => 0,
        ]);

        Storage::disk('public')->put('venue-photos/test.jpg', 'fake');

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('///filled.count.soap')
            ->assertSee('what3words.com/filled.count.soap')
            ->assertSee('venue-photos/test.jpg');
    }
}
