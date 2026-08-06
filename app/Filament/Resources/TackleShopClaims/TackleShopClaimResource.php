<?php

namespace App\Filament\Resources\TackleShopClaims;

use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;
use App\Filament\Resources\TackleShopClaims\Pages\ListTackleShopClaims;
use App\Filament\Resources\TackleShopClaims\Tables\TackleShopClaimsTable;
use App\Models\TackleShopClaim;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TackleShopClaimResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = TackleShopClaim::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $navigationLabel = 'Tackle shop claims';

    protected static ?int $navigationSort = 43;

    public static function table(Table $table): Table
    {
        return TackleShopClaimsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTackleShopClaims::route('/'),
        ];
    }
}
