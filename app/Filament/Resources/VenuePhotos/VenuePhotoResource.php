<?php

namespace App\Filament\Resources\VenuePhotos;

use App\Filament\Resources\VenuePhotos\Pages\CreateVenuePhoto;
use App\Filament\Resources\VenuePhotos\Pages\EditVenuePhoto;
use App\Filament\Resources\VenuePhotos\Pages\ListVenuePhotos;
use App\Filament\Resources\VenuePhotos\Schemas\VenuePhotoForm;
use App\Filament\Resources\VenuePhotos\Tables\VenuePhotosTable;
use App\Models\VenuePhoto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;

class VenuePhotoResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = VenuePhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Venue photos';

    protected static ?string $modelLabel = 'venue photo';

    protected static ?string $pluralModelLabel = 'venue photos';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return VenuePhotoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenuePhotosTable::configure($table);
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
            'index' => ListVenuePhotos::route('/'),
            'create' => CreateVenuePhoto::route('/create'),
            'edit' => EditVenuePhoto::route('/{record}/edit'),
        ];
    }
}
