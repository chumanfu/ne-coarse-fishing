<?php

namespace App\Filament\Resources\MatchReports;

use App\Filament\Resources\MatchReports\Pages\CreateMatchReport;
use App\Filament\Resources\MatchReports\Pages\EditMatchReport;
use App\Filament\Resources\MatchReports\Pages\ListMatchReports;
use App\Filament\Resources\MatchReports\Schemas\MatchReportForm;
use App\Filament\Resources\MatchReports\Tables\MatchReportsTable;
use App\Models\MatchReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;

class MatchReportResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = MatchReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Match reports';

    protected static ?string $modelLabel = 'match report';

    protected static ?string $pluralModelLabel = 'match reports';

    protected static ?int $navigationSort = 22;

    public static function form(Schema $schema): Schema
    {
        return MatchReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MatchReportsTable::configure($table);
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
            'index' => ListMatchReports::route('/'),
            'create' => CreateMatchReport::route('/create'),
            'edit' => EditMatchReport::route('/{record}/edit'),
        ];
    }
}
