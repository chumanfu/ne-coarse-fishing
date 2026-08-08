<?php

namespace App\Filament\Resources\WaterVideos;

use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;
use App\Filament\Resources\WaterVideos\Pages\CreateWaterVideo;
use App\Filament\Resources\WaterVideos\Pages\EditWaterVideo;
use App\Filament\Resources\WaterVideos\Pages\ListWaterVideos;
use App\Filament\Resources\WaterVideos\Schemas\WaterVideoForm;
use App\Filament\Resources\WaterVideos\Tables\WaterVideosTable;
use App\Models\WaterVideo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WaterVideoResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = WaterVideo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Water videos';

    protected static ?string $modelLabel = 'water video';

    protected static ?string $pluralModelLabel = 'water videos';

    protected static ?int $navigationSort = 26;

    public static function form(Schema $schema): Schema
    {
        return WaterVideoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaterVideosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaterVideos::route('/'),
            'create' => CreateWaterVideo::route('/create'),
            'edit' => EditWaterVideo::route('/{record}/edit'),
        ];
    }
}
