<?php

namespace Tests\Feature;

use App\Models\FishingSession;
use App\Models\Species;
use App\Models\User;
use App\Models\Venue;
use App\Models\Water;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SessionFormStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_session_with_empty_default_catch_row(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();

        $response = $this->actingAs($user)->from(route('sessions.create'))->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => 'all',
            'peg_mode' => 'existing',
            'water_peg_id' => '',
            'fished_at' => now()->toDateString(),
            'duration_hours' => '',
            'weather' => '',
            'peg_number' => '',
            'peg_name' => '',
            'peg_map_x' => '',
            'peg_map_y' => '',
            'commentary' => 'Quiet morning.',
            'tactics_tip' => '',
            'catches' => [
                ['species_id' => '', 'weight_lb' => '', 'weight_oz' => '', 'bait' => '', 'quantity' => 1],
            ],
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertSame('Quiet morning.', $session->commentary);
        $this->assertCount(0, $session->catches);
    }

    public function test_can_save_session_with_filled_catch_row(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();
        $species = Species::factory()->create([
            'name' => 'Test Roach',
            'slug' => 'test-roach-'.uniqid(),
        ]);

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => 'all',
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'catches' => [
                ['species_id' => '', 'weight_lb' => '', 'weight_oz' => '', 'bait' => '', 'quantity' => 1],
                [
                    'species_id' => $species->id,
                    'entry_type' => 'bag',
                    'entered_unit' => 'lb_oz',
                    'weight_lb' => '3',
                    'weight_oz' => '8',
                    'bait' => 'Maggot',
                    'quantity' => 2,
                ],
            ],
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertDatabaseHas('session_catches', [
            'fishing_session_id' => $session->id,
            'species_id' => $species->id,
            'entry_type' => 'bag',
            'weight_g' => 1588,
            'bait' => 'Maggot',
            'quantity' => 2,
        ]);
        $this->assertCount(1, $session->catches);
    }

    public function test_can_log_an_individual_fish_in_pounds_and_ounces(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();
        $species = Species::factory()->create([
            'name' => 'Test Carp',
            'slug' => 'test-carp-'.uniqid(),
        ]);

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'weight_unit' => 'lb_oz',
            'catches' => [[
                'species_id' => $species->id,
                'entry_type' => 'individual',
                'entered_unit' => 'lb_oz',
                'weight_lb' => '18',
                'weight_oz' => '4',
                'bait' => 'Boilie',
                'quantity' => 1,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('session_catches', [
            'species_id' => $species->id,
            'entry_type' => 'individual',
            'weight_g' => 8278,
            'quantity' => 1,
            'is_notable' => 0,
            'entered_unit' => 'lb_oz',
            'bait' => 'Boilie',
        ]);
        $this->assertSame('lb_oz', $user->fresh()->preferred_weight_unit);
    }

    public function test_can_log_a_fish_in_kilograms(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();
        $species = Species::factory()->create([
            'name' => 'Test Mirror',
            'slug' => 'test-mirror-'.uniqid(),
        ]);

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'weight_unit' => 'kg',
            'catches' => [[
                'species_id' => $species->id,
                'entry_type' => 'individual',
                'entered_unit' => 'kg',
                'weight_kg' => '5.67',
                'bait' => 'Maize',
                'quantity' => 1,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('session_catches', [
            'species_id' => $species->id,
            'weight_g' => 5670,
            'entered_unit' => 'kg',
        ]);
        $this->assertSame('kg', $user->fresh()->preferred_weight_unit);
    }

    public function test_can_log_a_bag_total_and_a_standout_fish(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();
        $roach = Species::factory()->create(['name' => 'Test Roach Bag', 'slug' => 'test-roach-bag-'.uniqid()]);
        $carp = Species::factory()->create(['name' => 'Test Carp Bag', 'slug' => 'test-carp-bag-'.uniqid()]);

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'weight_unit' => 'lb_oz',
            'catches' => [
                [
                    'species_id' => $roach->id,
                    'entry_type' => 'bag',
                    'entered_unit' => 'lb_oz',
                    'weight_lb' => '12',
                    'weight_oz' => '8',
                    'quantity' => 40,
                    'bait' => 'Maggot',
                    'is_notable' => '0',
                ],
                [
                    'species_id' => $carp->id,
                    'entry_type' => 'individual',
                    'entered_unit' => 'lb_oz',
                    'weight_lb' => '8',
                    'weight_oz' => '2',
                    'quantity' => 1,
                    'bait' => 'Boilie',
                    'is_notable' => '1',
                ],
            ],
        ])->assertRedirect();

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $this->assertSame(40, $session->fishCount());
        $this->assertDatabaseHas('session_catches', [
            'fishing_session_id' => $session->id,
            'species_id' => $roach->id,
            'entry_type' => 'bag',
            'quantity' => 40,
            'weight_g' => 5670,
        ]);
        $this->assertDatabaseHas('session_catches', [
            'fishing_session_id' => $session->id,
            'species_id' => $carp->id,
            'entry_type' => 'individual',
            'is_notable' => 1,
            'quantity' => 1,
        ]);
    }

    public function test_ounces_only_weights_are_accepted(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();
        $species = Species::factory()->create(['name' => 'Test Perch', 'slug' => 'test-perch-'.uniqid()]);

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'catches' => [[
                'species_id' => $species->id,
                'entry_type' => 'individual',
                'entered_unit' => 'lb_oz',
                'weight_lb' => '',
                'weight_oz' => '8',
                'quantity' => 1,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('session_catches', [
            'species_id' => $species->id,
            'weight_g' => 227,
        ]);
    }

    public function test_blanked_session_still_remembers_the_weight_unit(): void
    {
        $user = User::factory()->create(['preferred_weight_unit' => 'lb_oz']);
        $venue = Venue::factory()->create();

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'weight_unit' => 'kg',
        ])->assertRedirect();

        $this->assertSame('kg', $user->fresh()->preferred_weight_unit);
    }

    public function test_can_upload_session_photos_on_create(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);

        $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => 'all',
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'photos' => [
                UploadedFile::fake()->image('bank.jpg'),
                UploadedFile::fake()->image('catch.jpg'),
            ],
        ])->assertRedirect();

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $this->assertCount(2, $session->photos);
        $session->photos->each(fn ($photo) => Storage::disk(Uploads::diskName())->assertExists($photo->image_path));
    }

    public function test_can_upload_session_photos_on_update(): void
    {
        Storage::fake(Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $session = FishingSession::factory()->for($user)->for($venue)->create();

        $this->actingAs($user)->patch(route('sessions.update', $session), [
            'venue_id' => $venue->id,
            'peg_mode' => 'none',
            'fished_at' => $session->fished_at->toDateString(),
            'photos' => [UploadedFile::fake()->image('new.jpg')],
        ])->assertRedirect(route('sessions.show', $session));

        $session->refresh();
        $this->assertCount(1, $session->photos);
        Storage::disk(Uploads::diskName())->assertExists($session->photos->first()->image_path);
    }

    public function test_forget_empty_uploads_keeps_symfony_uploaded_files(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'necf-session-photo');
        file_put_contents($tmp, 'fake-image-bytes');

        $symfonyFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            $tmp,
            'bank.jpg',
            'image/jpeg',
            null,
            true,
        );

        $request = \Illuminate\Http\Request::create('/sessions', 'POST');
        $request->files->set('photos', $symfonyFile);

        $controller = app(\App\Http\Controllers\FishingSessionController::class);
        $method = new \ReflectionMethod($controller, 'forgetEmptyUploads');
        $method->setAccessible(true);
        $method->invoke($controller, $request, ['photos']);

        $this->assertCount(1, $request->file('photos', []));
    }

    public function test_empty_photo_file_input_does_not_block_save(): void
    {
        $user = User::factory()->create();
        $venue = Venue::factory()->create();
        $emptyPhoto = new UploadedFile(
            sys_get_temp_dir().'/necf-empty-session-photo',
            '',
            'application/octet-stream',
            UPLOAD_ERR_NO_FILE,
            true,
        );

        $response = $this->actingAs($user)->from(route('sessions.create'))->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => 'all',
            'peg_mode' => 'existing',
            'water_peg_id' => '',
            'fished_at' => now()->toDateString(),
            'photos' => [$emptyPhoto],
            'peg_photos' => [$emptyPhoto],
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertCount(0, $session->photos);
    }

    public function test_invalid_session_redirects_back_to_the_form_instead_of_a_422_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('sessions.create'))
            ->post(route('sessions.store'), [
                'venue_id' => '',
                'water_id' => 'all',
                'peg_mode' => 'existing',
                'fished_at' => '',
            ])
            ->assertRedirect(route('sessions.create'))
            ->assertSessionHasErrors(['venue_id', 'fished_at']);
    }

    public function test_stale_peg_fields_are_ignored_when_peg_mode_is_not_new(): void
    {
        Storage::fake(\App\Support\Uploads::diskName());

        $user = User::factory()->create();
        $venue = Venue::factory()->create(['is_approved' => true]);
        $water = Water::factory()->for($venue)->create([
            'map_image_path' => 'water-maps/pond.jpg',
        ]);
        Storage::disk(\App\Support\Uploads::diskName())->put('water-maps/pond.jpg', 'fake');

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'venue_id' => $venue->id,
            'water_id' => 'all',
            'peg_mode' => 'none',
            'fished_at' => now()->toDateString(),
            'peg_number' => 'Should not create peg',
            'peg_name' => 'Also ignored',
            'peg_map_x' => 12.5,
            'peg_map_y' => 34.5,
        ]);

        $session = FishingSession::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertNull($session->water_peg_id);
        $this->assertSame('Should not create peg', $session->peg_number);
        $this->assertSame(0, \App\Models\WaterPeg::query()->where('water_id', $water->id)->count());
    }
}
