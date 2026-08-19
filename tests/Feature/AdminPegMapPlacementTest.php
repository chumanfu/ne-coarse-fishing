<?php

namespace Tests\Feature;

use App\Filament\Resources\WaterPegs\Pages\CreateWaterPeg;
use App\Filament\Resources\WaterPegs\WaterPegResource;
use App\Models\User;
use App\Models\Water;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPegMapPlacementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_form_shows_map_after_selecting_water_with_image(): void
    {
        Storage::fake(Uploads::diskName());
        Role::findOrCreate('super_admin');

        $admin = User::factory()->create();
        $admin->syncRoles(['super_admin']);
        $water = Water::factory()->create([
            'name' => 'Island Lake',
            'map_image_path' => 'water-maps/island.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/island.jpg', 'fake');

        $this->actingAs($admin);

        Livewire::actingAs($admin)
            ->test(CreateWaterPeg::class)
            ->assertSee('Select a water first to place the peg on its pond map')
            ->fillForm([
                'water_id' => $water->id,
            ])
            ->assertSee($water->mapImageUrl(), false)
            ->assertSee('Zoom in')
            ->assertSee('pondMapPlacer')
            ->assertDontSee('Select a water first to place the peg on its pond map')
            ->assertDontSee('Upload a pond map image on the water record first');
    }

    public function test_admin_create_form_renders_peg_pin_span_with_correct_classes(): void
    {
        Storage::fake(Uploads::diskName());
        Role::findOrCreate('super_admin');

        $admin = User::factory()->create();
        $admin->syncRoles(['super_admin']);
        $water = Water::factory()->create([
            'name' => 'Test Lake',
            'map_image_path' => 'water-maps/test.jpg',
        ]);
        Storage::disk(Uploads::diskName())->put('water-maps/test.jpg', 'fake');

        $this->actingAs($admin);

        Livewire::actingAs($admin)
            ->test(CreateWaterPeg::class)
            ->fillForm(['water_id' => $water->id])
            // Pin span must carry the peg-pin class so scoped Filament CSS can style it.
            ->assertSee('peg-pin', false)
            // Wrapper must carry fi-pond-map-placer so scoped CSS selectors apply.
            ->assertSee('fi-pond-map-placer', false)
            // Image must carry max-h-96 (used on public site; Filament CSS override targets it via fi-pond-map-placer img).
            ->assertSee('max-h-96', false);
    }

    public function test_admin_create_form_warns_when_water_has_no_map(): void
    {
        Role::findOrCreate('super_admin');

        $admin = User::factory()->create();
        $admin->syncRoles(['super_admin']);
        $water = Water::factory()->create([
            'map_image_path' => null,
        ]);

        $this->actingAs($admin);

        Livewire::actingAs($admin)
            ->test(CreateWaterPeg::class)
            ->fillForm([
                'water_id' => $water->id,
            ])
            ->assertSee('Upload a pond map image on the water record first')
            ->assertDontSee('pondMapPlacer');

        $this->get(WaterPegResource::getUrl('create'))->assertOk();
    }
}
