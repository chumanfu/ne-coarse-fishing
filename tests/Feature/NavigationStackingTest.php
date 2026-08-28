<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationStackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_creates_stacking_context_above_page_content(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="relative z-50 bg-paper-bright/95 backdrop-blur-sm border-b border-[#d6cfc2]"', false)
            ->assertSee('home-hero', false);
    }

    public function test_authenticated_profile_dropdown_retains_elevated_z_index(): void
    {
        $user = User::factory()->create(['name' => 'Stack Test User']);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('class="absolute z-50 mt-2', false)
            ->assertSee('Stack Test User', false);
    }

    public function test_mobile_navigation_markup_is_unchanged(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="hidden lg:hidden border-t border-[#d6cfc2] bg-paper-bright"', false)
            ->assertSee('aria-label="Toggle navigation"', false);
    }
}
