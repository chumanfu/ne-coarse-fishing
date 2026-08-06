<?php

namespace App\Filament\Resources\TackleReviews;

use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;
use App\Filament\Resources\TackleReviews\Pages\CreateTackleReview;
use App\Filament\Resources\TackleReviews\Pages\EditTackleReview;
use App\Filament\Resources\TackleReviews\Pages\ListTackleReviews;
use App\Filament\Resources\TackleReviews\Schemas\TackleReviewForm;
use App\Filament\Resources\TackleReviews\Tables\TackleReviewsTable;
use App\Models\TackleReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TackleReviewResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = TackleReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Tackle reviews';

    protected static ?string $modelLabel = 'tackle review';

    protected static ?string $pluralModelLabel = 'tackle reviews';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return TackleReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TackleReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTackleReviews::route('/'),
            'create' => CreateTackleReview::route('/create'),
            'edit' => EditTackleReview::route('/{record}/edit'),
        ];
    }
}
