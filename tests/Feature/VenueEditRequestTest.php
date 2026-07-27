<?php

namespace Tests\Feature;

use App\Livewire\VenueWizard;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueEditRequest;
use App\Services\VenuePersistenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VenueEditRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_angler_can_submit_edit_request(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('angler');
        $user->assignRole('angler');

        $venue = Venue::factory()->create(['is_approved' => true]);

        $this->actingAs($user)
            ->get(route('venues.suggest-edit', $venue))
            ->assertOk();

        Livewire::actingAs($user)
            ->test(VenueWizard::class, ['venue' => $venue, 'editRequest' => true])
            ->set('step', 5)
            ->set('locationSet', true)
            ->set('name', 'Updated Lake Name')
            ->set('overview', 'Updated overview.')
            ->set('ticket_type', 'day_ticket')
            ->set('waters.0.name', 'Main lake')
            ->set('editRequestMessage', 'Fixed the peg count.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('venues.show', $venue));

        $this->assertDatabaseHas('venue_edit_requests', [
            'venue_id' => $venue->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $request = VenueEditRequest::query()->first();
        $this->assertSame('Updated Lake Name', $request->proposed_data['venue']['name']);
        $this->assertSame('Main lake', $request->proposed_data['waters'][0]['name']);
    }

    public function test_admin_can_approve_edit_request(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('super_admin');
        $admin->assignRole('super_admin');

        $venue = Venue::factory()->create(['name' => 'Before Edit', 'overview' => 'Old overview']);

        $editRequest = VenueEditRequest::query()->create([
            'venue_id' => $venue->id,
            'user_id' => User::factory()->create()->id,
            'proposed_data' => [
                'venue' => [
                    'name' => 'After Edit',
                    'overview' => 'New overview',
                    'latitude' => $venue->latitude,
                    'longitude' => $venue->longitude,
                    'ticket_type' => $venue->ticket_type,
                    'is_complex' => false,
                ],
                'waters' => [[
                    'name' => 'Main',
                    'description' => null,
                    'peg_count' => null,
                    'depth_info' => null,
                    'species' => [],
                ]],
            ],
            'status' => 'pending',
        ]);

        app(VenuePersistenceService::class)->apply($venue, $editRequest->proposed_data);
        $editRequest->update(['status' => 'approved', 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);

        $venue->refresh();
        $this->assertSame('After Edit', $venue->name);
        $this->assertSame('New overview', $venue->overview);
    }

    public function test_comparison_builds_before_and_after_snapshots(): void
    {
        $venue = Venue::factory()->create([
            'name' => 'Before Name',
            'overview' => 'Before overview',
        ]);

        $water = $venue->waters()->create(['name' => 'Main Lake', 'sort_order' => 0]);

        $request = VenueEditRequest::query()->create([
            'venue_id' => $venue->id,
            'user_id' => User::factory()->create()->id,
            'proposed_data' => [
                'venue' => [
                    'name' => 'After Name',
                    'overview' => 'After overview',
                    'latitude' => $venue->latitude,
                    'longitude' => $venue->longitude,
                    'ticket_type' => $venue->ticket_type,
                    'is_complex' => false,
                ],
                'waters' => [[
                    'id' => $water->id,
                    'name' => 'Main Lake',
                    'description' => 'New description',
                    'peg_count' => 12,
                    'depth_info' => null,
                    'species' => [],
                ]],
            ],
            'status' => 'pending',
        ]);

        $comparison = app(\App\Services\VenueEditRequestComparison::class)->build($request);

        $nameField = collect($comparison['fields'])->firstWhere('label', 'Name');
        $this->assertSame('Before Name', $nameField['before']);
        $this->assertSame('After Name', $nameField['after']);
        $this->assertTrue($nameField['changed']);

        $this->assertSame('Before overview', collect($comparison['fields'])->firstWhere('label', 'Overview')['before']);
        $this->assertTrue($comparison['waters'][0]['changed']);
        $this->assertStringContainsString('New description', $comparison['waters'][0]['after']);
    }

    public function test_species_order_only_does_not_flag_water_as_changed(): void
    {
        $venue = Venue::factory()->create();
        $water = $venue->waters()->create(['name' => 'Bolam Lake', 'depth_info' => 'Varied', 'sort_order' => 0]);

        $tench = \App\Models\Species::query()->where('name', 'Tench')->firstOrFail();
        $bream = \App\Models\Species::query()->where('name', 'Bream')->firstOrFail();
        $roach = \App\Models\Species::query()->where('name', 'Roach')->firstOrFail();
        $perch = \App\Models\Species::query()->where('name', 'Perch')->firstOrFail();
        $pike = \App\Models\Species::query()->where('name', 'Pike')->firstOrFail();

        $water->species()->sync([$tench->id, $bream->id, $roach->id, $perch->id, $pike->id]);

        $request = VenueEditRequest::query()->create([
            'venue_id' => $venue->id,
            'user_id' => User::factory()->create()->id,
            'proposed_data' => [
                'venue' => ['name' => $venue->name, 'ticket_type' => $venue->ticket_type, 'is_complex' => false],
                'waters' => [[
                    'id' => $water->id,
                    'name' => 'Bolam Lake',
                    'description' => $water->description,
                    'peg_count' => $water->peg_count,
                    'depth_info' => 'Varied',
                    'species' => [$bream->id, $perch->id, $pike->id, $roach->id, $tench->id],
                ]],
            ],
            'status' => 'pending',
        ]);

        $comparison = app(\App\Services\VenueEditRequestComparison::class)->build($request);

        $this->assertFalse($comparison['waters'][0]['changed']);
        $this->assertSame('unchanged', $comparison['waters'][0]['status']);
        $this->assertStringContainsString('Species: Bream, Perch, Pike, Roach, Tench', $comparison['waters'][0]['before']);
        $this->assertStringContainsString('Species: Bream, Perch, Pike, Roach, Tench', $comparison['waters'][0]['after']);
    }
}
