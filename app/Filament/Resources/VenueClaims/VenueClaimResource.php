<?php

namespace App\Filament\Resources\VenueClaims;

use App\Filament\Resources\VenueClaims\Pages\CreateVenueClaim;
use App\Filament\Resources\VenueClaims\Pages\EditVenueClaim;
use App\Filament\Resources\VenueClaims\Pages\ListVenueClaims;
use App\Filament\Resources\VenueClaims\Schemas\VenueClaimForm;
use App\Filament\Resources\VenueClaims\Tables\VenueClaimsTable;
use App\Models\VenueClaim;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VenueClaimResource extends Resource
{
    protected static ?string $model = VenueClaim::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return VenueClaimForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenueClaimsTable::configure($table);
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
            'index' => ListVenueClaims::route('/'),
            'create' => CreateVenueClaim::route('/create'),
            'edit' => EditVenueClaim::route('/{record}/edit'),
        ];
    }
}
