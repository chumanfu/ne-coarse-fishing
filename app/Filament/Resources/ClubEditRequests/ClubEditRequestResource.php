<?php

namespace App\Filament\Resources\ClubEditRequests;

use App\Filament\Resources\ClubEditRequests\Pages\ListClubEditRequests;
use App\Filament\Resources\ClubEditRequests\Pages\ViewClubEditRequest;
use App\Filament\Resources\ClubEditRequests\Tables\ClubEditRequestsTable;
use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;
use App\Models\ClubEditRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClubEditRequestResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = ClubEditRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $navigationLabel = 'Club edit requests';

    protected static ?int $navigationSort = 44;

    public static function canView($record): bool
    {
        return auth()->user()?->can('view', $record) ?? false;
    }

    public static function table(Table $table): Table
    {
        return ClubEditRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubEditRequests::route('/'),
            'view' => ViewClubEditRequest::route('/{record}'),
        ];
    }
}
