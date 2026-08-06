<?php

namespace App\Filament\Resources\TackleShopEditRequests;

use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;
use App\Filament\Resources\TackleShopEditRequests\Pages\ListTackleShopEditRequests;
use App\Filament\Resources\TackleShopEditRequests\Pages\ViewTackleShopEditRequest;
use App\Filament\Resources\TackleShopEditRequests\Tables\TackleShopEditRequestsTable;
use App\Models\TackleShopEditRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TackleShopEditRequestResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = TackleShopEditRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $navigationLabel = 'Tackle shop edits';

    protected static ?int $navigationSort = 45;

    public static function canView($record): bool
    {
        return auth()->user()?->can('view', $record) ?? false;
    }

    public static function table(Table $table): Table
    {
        return TackleShopEditRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTackleShopEditRequests::route('/'),
            'view' => ViewTackleShopEditRequest::route('/{record}'),
        ];
    }
}
