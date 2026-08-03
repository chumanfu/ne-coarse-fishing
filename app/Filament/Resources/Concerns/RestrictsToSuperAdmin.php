<?php

namespace App\Filament\Resources\Concerns;

trait RestrictsToSuperAdmin
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
