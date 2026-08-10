<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\TackleShop;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryUrlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_venues_have_urls_and_fake_beamish_is_absent(): void
    {
        $this->assertNull(Venue::query()->where('slug', 'beamish-park-lake')->first());

        $sample = Venue::query()->where('slug', 'aldin-grange')->first();
        $this->assertNotNull($sample);
        $this->assertSame('https://aldingrangelakes.co.uk/', $sample->url);

        $missing = Venue::query()
            ->whereIn('slug', [
                'killingworth-lakes',
                'eden-grange',
                'angel-lakes',
                'derwent-reservoir',
                'bolam-lake',
            ])
            ->where(fn ($query) => $query->whereNull('url')->orWhere('url', ''))
            ->count();

        $this->assertSame(0, $missing);
    }

    public function test_seeded_clubs_and_tackle_shops_have_urls(): void
    {
        $this->assertSame(
            0,
            Club::query()->where(fn ($query) => $query->whereNull('url')->orWhere('url', ''))->count()
        );

        $this->assertSame(
            0,
            TackleShop::query()->where(fn ($query) => $query->whereNull('url')->orWhere('url', ''))->count()
        );

        $this->assertSame(
            'https://leazesangling.com/',
            Club::query()->where('slug', 'leazes-park-angling-club')->value('url')
        );

        $this->assertSame(
            'https://billysfishing.co.uk/',
            TackleShop::query()->where('slug', 'billys-fishing-tackle')->value('url')
        );

        $this->assertSame(
            'https://www.fishingtackleandbait.co.uk/',
            TackleShop::query()->where('slug', 'fishing-tackle-and-bait')->value('url')
        );

        $this->assertSame(
            'https://willyworms.co.uk/',
            TackleShop::query()->where('slug', 'willy-worms')->value('url')
        );

        $this->assertSame(
            'https://www.birdstackle.co.uk/',
            TackleShop::query()->where('slug', 'birds-tackle')->value('url')
        );
    }
}
