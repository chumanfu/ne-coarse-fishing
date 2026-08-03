<?php

namespace App\Filament\Resources\WaterPegs;

use App\Filament\Resources\WaterPegs\Pages\CreateWaterPeg;
use App\Filament\Resources\WaterPegs\Pages\EditWaterPeg;
use App\Filament\Resources\WaterPegs\Pages\ListWaterPegs;
use App\Filament\Resources\WaterPegs\Schemas\WaterPegForm;
use App\Filament\Resources\WaterPegs\Tables\WaterPegsTable;
use App\Models\WaterPeg;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;

class WaterPegResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = WaterPeg::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Venues';

    protected static ?string $navigationLabel = 'Water pegs';

    protected static ?string $modelLabel = 'water peg';

    protected static ?string $pluralModelLabel = 'water pegs';

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return WaterPegForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaterPegsTable::configure($table);
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
            'index' => ListWaterPegs::route('/'),
            'create' => CreateWaterPeg::route('/create'),
            'edit' => EditWaterPeg::route('/{record}/edit'),
        ];
    }
}
