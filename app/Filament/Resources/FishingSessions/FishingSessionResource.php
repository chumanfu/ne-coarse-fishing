<?php

namespace App\Filament\Resources\FishingSessions;

use App\Filament\Resources\FishingSessions\Pages\CreateFishingSession;
use App\Filament\Resources\FishingSessions\Pages\EditFishingSession;
use App\Filament\Resources\FishingSessions\Pages\ListFishingSessions;
use App\Filament\Resources\FishingSessions\Schemas\FishingSessionForm;
use App\Filament\Resources\FishingSessions\Tables\FishingSessionsTable;
use App\Models\FishingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FishingSessionResource extends Resource
{
    protected static ?string $model = FishingSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return FishingSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FishingSessionsTable::configure($table);
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
            'index' => ListFishingSessions::route('/'),
            'create' => CreateFishingSession::route('/create'),
            'edit' => EditFishingSession::route('/{record}/edit'),
        ];
    }
}
