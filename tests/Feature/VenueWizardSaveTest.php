<?php

namespace Tests\Feature;

use App\Filament\Resources\Venues\VenueResource;
use App\Livewire\VenueWizard;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VenueWizardSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_edit_page_resolves_by_id_and_slug(): void
    {
        $admin = $this->makeAdmin();
        $venue = Venue::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->get('/admin/venues/'.$venue->id.'/edit')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/venues/'.$venue->slug.'/edit')
            ->assertOk();
    }

    public function test_admin_wizard_save_redirects_to_edit_by_id(): void
    {
        $admin = $this->makeAdmin();

        Livewire::actingAs($admin)
            ->test(VenueWizard::class, ['admin' => true])
            ->set('step', 5)
            ->set('locationSet', true)
            ->set('latitude', 54.78)
            ->set('longitude', -1.57)
            ->set('name', 'Wizard Save Test Lakes')
            ->set('overview', 'A test venue overview.')
            ->set('address', 'Durham')
            ->set('ticket_type', 'day_ticket')
            ->set('waters.0.name', 'Main lake')
            ->set('is_approved', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(VenueResource::getUrl('edit', ['record' => Venue::query()->where('name', 'Wizard Save Test Lakes')->value('id')]));

        $this->assertDatabaseHas('venues', [
            'name' => 'Wizard Save Test Lakes',
            'is_approved' => true,
        ]);
    }

    public function test_public_wizard_save_redirects_to_show(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('angler');
        $user->assignRole('angler');

        Livewire::actingAs($user)
            ->test(VenueWizard::class)
            ->set('step', 5)
            ->set('locationSet', true)
            ->set('latitude', 54.78)
            ->set('longitude', -1.57)
            ->set('name', 'Public Submit Pond')
            ->set('overview', 'Submitted from the public wizard.')
            ->set('address', 'Newcastle')
            ->set('ticket_type', 'day_ticket')
            ->set('waters.0.name', 'Pond')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('venues.show', Venue::query()->where('name', 'Public Submit Pond')->first()));

        $this->assertDatabaseHas('venues', [
            'name' => 'Public Submit Pond',
            'is_approved' => false,
            'user_id' => $user->id,
        ]);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create([
            'email' => 'admin-test@nefishing.test',
        ]);

        Role::findOrCreate('super_admin');
        $admin->assignRole('super_admin');

        return $admin;
    }
}
