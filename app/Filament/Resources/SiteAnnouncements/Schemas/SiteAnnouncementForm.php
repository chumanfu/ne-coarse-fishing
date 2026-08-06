<?php

namespace App\Filament\Resources\SiteAnnouncements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteAnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(fn () => auth()->id())
                    ->required()
                    ->searchable(),
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('body')->required()->rows(5)->columnSpanFull(),
                Select::make('level')
                    ->options([
                        'info' => 'Notice',
                        'warning' => 'Warning',
                        'maintenance' => 'Maintenance',
                    ])
                    ->required()
                    ->default('info')
                    ->native(false),
                DateTimePicker::make('starts_at')
                    ->label('Show from')
                    ->required()
                    ->default(now()),
                DateTimePicker::make('ends_at')
                    ->label('Hide after')
                    ->required()
                    ->helperText('Site announcements must have a maximum display window.')
                    ->default(now()->addDays(2)),
                Toggle::make('is_active')->label('Active')->default(true),
            ]);
    }
}
