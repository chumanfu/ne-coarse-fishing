<?php

namespace App\Filament\Resources\Waters;

use App\Filament\Resources\Waters\Pages\CreateWater;
use App\Filament\Resources\Waters\Pages\EditWater;
use App\Filament\Resources\Waters\Pages\ListWaters;
use App\Filament\Resources\Waters\Schemas\WaterForm;
use App\Filament\Resources\Waters\Tables\WatersTable;
use App\Models\Water;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;

class WaterResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = Water::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|\UnitEnum|null $navigationGroup = 'Venues';

    protected static ?string $navigationLabel = 'Waters';

    protected static ?string $modelLabel = 'water';

    protected static ?string $pluralModelLabel = 'waters';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return WaterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WatersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaters::route('/'),
            'create' => CreateWater::route('/create'),
            'edit' => EditWater::route('/{record}/edit'),
        ];
    }
}
