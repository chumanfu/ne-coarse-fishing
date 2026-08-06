<?php

namespace App\Filament\Resources\ClubClaims;

use App\Filament\Resources\ClubClaims\Pages\ListClubClaims;
use App\Filament\Resources\ClubClaims\Tables\ClubClaimsTable;
use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;
use App\Models\ClubClaim;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClubClaimResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = ClubClaim::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $navigationLabel = 'Club claims';

    protected static ?int $navigationSort = 42;

    public static function table(Table $table): Table
    {
        return ClubClaimsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubClaims::route('/'),
        ];
    }
}
