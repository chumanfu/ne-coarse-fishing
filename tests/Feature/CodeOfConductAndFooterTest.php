<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeOfConductAndFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_code_of_conduct_page_is_public_and_covers_abuse_and_gdpr(): void
    {
        $this->get(route('code-of-conduct'))
            ->assertOk()
            ->assertSee('Code of conduct &amp; privacy', false)
            ->assertSee('Online abuse of any nature is not permitted', false)
            ->assertSee('Your GDPR rights', false)
            ->assertSee('Download a copy of your data', false)
            ->assertSee('How we use your data', false)
            ->assertSee(route('contact.create', absolute: false), false);
    }

    public function test_footer_site_map_appears_on_public_pages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="Site map"', false)
            ->assertSee('Code of conduct &amp; privacy', false)
            ->assertSee(route('venues.index', absolute: false), false)
            ->assertSee(route('map.index', absolute: false), false)
            ->assertSee(route('code-of-conduct', absolute: false), false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('aria-label="Site map"', false)
            ->assertSee(route('code-of-conduct', absolute: false), false);
    }
}
