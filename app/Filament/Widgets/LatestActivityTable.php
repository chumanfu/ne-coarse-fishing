<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use App\Models\User;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestActivityTable extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Site activity';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public function table(Table $table): Table
    {
        return ActivitiesTable::configure(
            $table
                ->query(fn (): Builder => Activity::query()->with('user'))
                ->heading('Site activity')
                ->description('Search and filter everything happening across the site.')
                ->paginationMode(PaginationMode::Default)
        );
    }
}
