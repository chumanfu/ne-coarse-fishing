<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_promotes_featured_clubs(): void
    {
        Club::query()->update(['is_featured' => false]);

        Club::factory()->featured()->create([
            'name' => 'Featured NE Club',
            'sort_order' => 1,
        ]);

        Club::factory()->create([
            'name' => 'Hidden Club',
            'is_featured' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Angling clubs')
            ->assertSee('Featured NE Club')
            ->assertSee('Latest activity');
    }

    public function test_index_lists_published_clubs(): void
    {
        Club::factory()->create(['name' => 'Public Club', 'town' => 'Durham']);
        Club::factory()->unpublished()->create(['name' => 'Draft Club']);

        $this->get(route('clubs.index'))
            ->assertOk()
            ->assertSee('Public Club')
            ->assertDontSee('Draft Club');
    }

    public function test_registration_can_select_club_memberships(): void
    {
        $clubA = Club::factory()->create(['name' => 'Club Alpha']);
        $clubB = Club::factory()->create(['name' => 'Club Beta']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Club memberships')
            ->assertSee('Club Alpha');

        $response = $this->post('/register', [
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'club_ids' => [$clubA->id, $clubB->id],
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'member@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEqualsCanonicalizing([$clubA->id, $clubB->id], $user->clubs()->pluck('clubs.id')->all());
    }

    public function test_profile_can_update_club_memberships(): void
    {
        $user = User::factory()->create();
        $keep = Club::factory()->create(['name' => 'Keep Club']);
        $drop = Club::factory()->create(['name' => 'Drop Club']);
        $add = Club::factory()->create(['name' => 'Add Club']);
        $user->clubs()->sync([$keep->id, $drop->id]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'club_ids' => [$keep->id, $add->id],
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertEqualsCanonicalizing([$keep->id, $add->id], $user->fresh()->clubs()->pluck('clubs.id')->all());
    }

    public function test_seeded_clubs_have_logos_linked(): void
    {
        $club = Club::query()->where('slug', 'tyne-anglers-alliance')->first();

        $this->assertNotNull($club);
        $this->assertNotNull($club->logo_path);
        $this->assertStringContainsString('/images/clubs/', (string) $club->logoUrl());
    }

    public function test_index_shows_club_logos(): void
    {
        Club::factory()->create([
            'name' => 'Logo Club',
            'logo_path' => 'images/clubs/tyne-anglers-alliance.png',
            'is_published' => true,
        ]);

        $this->get(route('clubs.index'))
            ->assertOk()
            ->assertSee('Logo Club')
            ->assertSee('images/clubs/tyne-anglers-alliance.png', false);
    }
}
