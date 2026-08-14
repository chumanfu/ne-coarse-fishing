<?php

namespace Tests\Feature;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayNightModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layouts_force_light_mode_without_a_theme_toggle(): void
    {
        $venue = Venue::factory()->create(['is_approved' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee("document.documentElement.classList.remove('dark')", false)
            ->assertSee("document.documentElement.style.colorScheme = 'light'", false)
            ->assertSee("localStorage.removeItem('necf-theme')", false)
            ->assertDontSee('data-theme-toggle', false)
            ->assertDontSee('$store.theme.toggle()', false)
            ->assertSee('dark:bg-slate-950', false);

        $this->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee("localStorage.removeItem('necf-theme')", false)
            ->assertDontSee('data-theme-toggle', false)
            ->assertDontSee('$store.theme.toggle()', false)
            ->assertSee('dark:text-sky-300', false)
            ->assertSee('dark:text-emerald-300', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee("document.documentElement.classList.remove('dark')", false)
            ->assertSee("document.documentElement.style.colorScheme = 'light'", false)
            ->assertSee("localStorage.removeItem('necf-theme')", false)
            ->assertDontSee('data-theme-toggle', false)
            ->assertDontSee('$store.theme.toggle()', false);
    }
}
