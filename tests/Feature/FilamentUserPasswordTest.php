<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_password_when_editing_user(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create();
        $originalHash = $target->password;

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $target->getKey()])
            ->set('data.password', 'new-admin-set-password')
            ->set('data.passwordConfirmation', 'new-admin-set-password')
            ->call('save')
            ->assertHasNoErrors();

        $target->refresh();

        $this->assertNotSame($originalHash, $target->password);
        $this->assertTrue(Hash::check('new-admin-set-password', $target->password));
    }

    public function test_admin_can_create_user_with_password(): void
    {
        $admin = $this->makeAdmin();
        Role::findOrCreate('angler');

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->set('data.name', 'Filament Created User')
            ->set('data.email', 'filament-user@example.com')
            ->set('data.password', 'create-user-password')
            ->set('data.passwordConfirmation', 'create-user-password')
            ->set('data.roles', [Role::where('name', 'angler')->value('id')])
            ->call('create')
            ->assertHasNoErrors();

        $user = User::query()->where('email', 'filament-user@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('create-user-password', $user->password));
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        Role::findOrCreate('super_admin');
        $admin->assignRole('super_admin');

        return $admin;
    }
}
