<?php

namespace App\Filament\Resources\VenueEditRequests;

use App\Filament\Resources\VenueEditRequests\Pages\ListVenueEditRequests;
use App\Filament\Resources\VenueEditRequests\Pages\ViewVenueEditRequest;
use App\Filament\Resources\VenueEditRequests\Schemas\VenueEditRequestForm;
use App\Filament\Resources\VenueEditRequests\Tables\VenueEditRequestsTable;
use App\Models\VenueEditRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VenueEditRequestResource extends Resource
{
    protected static ?string $model = VenueEditRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $navigationLabel = 'Venue edit requests';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('super_admin') || $user->hasRole('fishery_manager'));
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view', $record) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return VenueEditRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenueEditRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVenueEditRequests::route('/'),
            'view' => ViewVenueEditRequest::route('/{record}'),
        ];
    }
}
