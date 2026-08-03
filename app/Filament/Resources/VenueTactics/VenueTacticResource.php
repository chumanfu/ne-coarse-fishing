<?php

namespace App\Filament\Resources\VenueTactics;

use App\Filament\Resources\VenueTactics\Pages\CreateVenueTactic;
use App\Filament\Resources\VenueTactics\Pages\EditVenueTactic;
use App\Filament\Resources\VenueTactics\Pages\ListVenueTactics;
use App\Filament\Resources\VenueTactics\Schemas\VenueTacticForm;
use App\Filament\Resources\VenueTactics\Tables\VenueTacticsTable;
use App\Models\VenueTactic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;

class VenueTacticResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = VenueTactic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Venue tactics';

    protected static ?string $modelLabel = 'venue tactic';

    protected static ?string $pluralModelLabel = 'venue tactics';

    protected static ?int $navigationSort = 24;

    public static function form(Schema $schema): Schema
    {
        return VenueTacticForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenueTacticsTable::configure($table);
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
            'index' => ListVenueTactics::route('/'),
            'create' => CreateVenueTactic::route('/create'),
            'edit' => EditVenueTactic::route('/{record}/edit'),
        ];
    }
}
