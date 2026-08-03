<?php

namespace App\Filament\Resources\TackleShops;

use App\Filament\Resources\TackleShops\Pages\CreateTackleShop;
use App\Filament\Resources\TackleShops\Pages\EditTackleShop;
use App\Filament\Resources\TackleShops\Pages\ListTackleShops;
use App\Filament\Resources\TackleShops\Schemas\TackleShopForm;
use App\Filament\Resources\TackleShops\Tables\TackleShopsTable;
use App\Models\TackleShop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TackleShopResource extends Resource
{
    protected static ?string $model = TackleShop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|\UnitEnum|null $navigationGroup = 'Directory';

    protected static ?string $navigationLabel = 'Tackle shops';

    protected static ?string $modelLabel = 'tackle shop';

    protected static ?string $pluralModelLabel = 'tackle shops';

    protected static ?int $navigationSort = 40;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return TackleShopForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TackleShopsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTackleShops::route('/'),
            'create' => CreateTackleShop::route('/create'),
            'edit' => EditTackleShop::route('/{record}/edit'),
        ];
    }
}
