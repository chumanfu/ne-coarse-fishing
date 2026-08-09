<?php

namespace Tests\Feature;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayNightModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layouts_include_theme_toggle_and_boot_script(): void
    {
        $venue = Venue::factory()->create(['is_approved' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('necf-theme', false)
            ->assertSee('Switch to night mode', false)
            ->assertSee('data-theme-toggle', false)
            ->assertSee('$store.theme.toggle()', false)
            ->assertSee('dark:bg-slate-950', false);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('necf-theme', false)
            ->assertSee('$store.theme.toggle()', false)
            ->assertSee('dark:text-sky-300', false)
            ->assertSee('dark:text-emerald-300', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('necf-theme', false)
            ->assertSee('$store.theme.toggle()', false);
    }
}
