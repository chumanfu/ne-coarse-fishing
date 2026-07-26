<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $adminRole = Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');
        Role::findOrCreate('fishery_manager');

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@nefishing.test'],
            [
                'name' => 'admin',
                'password' => Hash::make('Eleanor44336110'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$adminRole]);
    }

    public function down(): void
    {
        User::query()->where('email', 'admin@nefishing.test')->delete();
    }
};
