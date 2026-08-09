<?php

namespace Tests\Feature;

use App\Models\TackleShop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TackleShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_promotes_featured_tackle_shops(): void
    {
        TackleShop::query()->update(['is_featured' => false]);

        TackleShop::factory()->featured()->create([
            'name' => 'Featured NE Tackle',
            'url' => 'https://featured-ne-tackle.example/',
            'sort_order' => 1,
        ]);

        TackleShop::factory()->create([
            'name' => 'Hidden Shop',
            'is_featured' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tackle shops')
            ->assertSee('Featured NE Tackle')
            ->assertSee('https://featured-ne-tackle.example/', false)
            ->assertSee('Latest activity');
    }

    public function test_index_lists_published_shops_with_website_links(): void
    {
        TackleShop::factory()->create([
            'name' => 'Billy Local Shop',
            'url' => 'https://billy.example/',
            'location_type' => 'local',
            'town' => 'North Shields',
        ]);

        TackleShop::factory()->unpublished()->create([
            'name' => 'Draft Shop',
        ]);

        $this->get(route('tackle-shops.index'))
            ->assertOk()
            ->assertSee('Billy Local Shop')
            ->assertSee('https://billy.example/', false)
            ->assertDontSee('Draft Shop');
    }

    public function test_show_page_displays_shop_website(): void
    {
        $shop = TackleShop::factory()->create([
            'name' => 'Fishdeal Demo',
            'url' => 'https://www.fishdeal.co.uk/',
            'location_type' => 'online',
            'logo_path' => 'images/tackle-shops/fishdeal.png',
        ]);

        $this->get(route('tackle-shops.show', $shop))
            ->assertOk()
            ->assertSee('Fishdeal Demo')
            ->assertSee('https://www.fishdeal.co.uk/', false)
            ->assertSee('images/tackle-shops/fishdeal.png', false)
            ->assertSee('Visit website');
    }

    public function test_index_shows_shop_logos(): void
    {
        TackleShop::factory()->create([
            'name' => 'Logo Shop',
            'logo_path' => 'images/tackle-shops/ad-tackle.png',
            'is_published' => true,
        ]);

        $this->get(route('tackle-shops.index'))
            ->assertOk()
            ->assertSee('Logo Shop')
            ->assertSee('images/tackle-shops/ad-tackle.png', false);
    }

    public function test_seeded_shops_have_logos_linked(): void
    {
        $shop = TackleShop::query()->where('slug', 'billys-fishing-tackle')->first();

        $this->assertNotNull($shop);
        $this->assertSame('images/tackle-shops/billys-fishing-tackle.svg', $shop->logo_path);
        $this->assertStringContainsString('/images/tackle-shops/billys-fishing-tackle.svg', (string) $shop->logoUrl());
    }

    public function test_willy_worms_is_seeded_as_featured_hybrid_shop(): void
    {
        $shop = TackleShop::query()->where('slug', 'willy-worms')->first();

        $this->assertNotNull($shop);
        $this->assertSame('Willy Worms', $shop->name);
        $this->assertSame('https://willyworms.co.uk/', $shop->url);
        $this->assertSame('hybrid', $shop->location_type);
        $this->assertTrue($shop->is_featured);
        $this->assertTrue($shop->is_published);
        $this->assertSame('images/tackle-shops/willy-worms.png', $shop->logo_path);

        $this->get(route('tackle-shops.show', $shop))
            ->assertOk()
            ->assertSee('Willy Worms')
            ->assertSee('https://willyworms.co.uk/', false)
            ->assertSee('Baxter Hall Farm');
    }

    public function test_unpublished_shop_is_not_publicly_viewable(): void
    {
        $shop = TackleShop::factory()->unpublished()->create();

        $this->get(route('tackle-shops.show', $shop))->assertNotFound();
    }
}
