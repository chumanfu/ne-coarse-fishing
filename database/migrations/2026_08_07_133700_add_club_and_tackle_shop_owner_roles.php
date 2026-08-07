<?php

use App\Models\Club;
use App\Models\TackleShop;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Role::findOrCreate('club_owner');
        Role::findOrCreate('tackle_shop_owner');

        Club::query()
            ->whereNotNull('manager_id')
            ->pluck('manager_id')
            ->unique()
            ->each(function (int $userId): void {
                $user = User::query()->find($userId);

                if ($user) {
                    $user->assignRole('club_owner');
                }
            });

        TackleShop::query()
            ->whereNotNull('manager_id')
            ->pluck('manager_id')
            ->unique()
            ->each(function (int $userId): void {
                $user = User::query()->find($userId);

                if ($user) {
                    $user->assignRole('tackle_shop_owner');
                }
            });
    }

    public function down(): void
    {
        Role::query()->whereIn('name', ['club_owner', 'tackle_shop_owner'])->delete();
    }
};
