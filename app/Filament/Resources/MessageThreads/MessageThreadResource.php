<?php

namespace App\Filament\Resources\MessageThreads;

use App\Filament\Resources\Concerns\RestrictsToSuperAdmin;
use App\Filament\Resources\MessageThreads\Pages\CreateMessageThread;
use App\Filament\Resources\MessageThreads\Pages\ListMessageThreads;
use App\Filament\Resources\MessageThreads\Pages\ViewMessageThread;
use App\Filament\Resources\MessageThreads\Schemas\MessageThreadForm;
use App\Filament\Resources\MessageThreads\Tables\MessageThreadsTable;
use App\Models\MessageThread;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessageThreadResource extends Resource
{
    use RestrictsToSuperAdmin;

    protected static ?string $model = MessageThread::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $modelLabel = 'message';

    protected static ?string $pluralModelLabel = 'messages';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = MessageThread::query()
            ->where(function ($query) {
                $query->whereNull('admin_last_read_at')
                    ->orWhereColumn('admin_last_read_at', '<', 'last_message_at');
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return MessageThreadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessageThreadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessageThreads::route('/'),
            'create' => CreateMessageThread::route('/create'),
            'view' => ViewMessageThread::route('/{record}'),
        ];
    }
}
