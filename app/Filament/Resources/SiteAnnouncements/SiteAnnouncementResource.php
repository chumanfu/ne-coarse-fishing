<?php

namespace App\Filament\Resources\SiteAnnouncements;

use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;
use App\Filament\Resources\SiteAnnouncements\Pages\CreateSiteAnnouncement;
use App\Filament\Resources\SiteAnnouncements\Pages\EditSiteAnnouncement;
use App\Filament\Resources\SiteAnnouncements\Pages\ListSiteAnnouncements;
use App\Filament\Resources\SiteAnnouncements\Schemas\SiteAnnouncementForm;
use App\Filament\Resources\SiteAnnouncements\Tables\SiteAnnouncementsTable;
use App\Models\SiteAnnouncement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SiteAnnouncementResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = SiteAnnouncement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Site announcements';

    protected static ?string $modelLabel = 'site announcement';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return SiteAnnouncementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteAnnouncementsTable::configure($table);
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
            'index' => ListSiteAnnouncements::route('/'),
            'create' => CreateSiteAnnouncement::route('/create'),
            'edit' => EditSiteAnnouncement::route('/{record}/edit'),
        ];
    }
}
