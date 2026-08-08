<?php

namespace App\Filament\Resources\WaterVideos\Schemas;

use App\Models\Water;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WaterVideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('water_id')
                    ->label('Water')
                    ->relationship(
                        name: 'water',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->with('venue')->orderBy('name'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Water $record): string => $record->venue
                            ? "{$record->name} ({$record->venue->name})"
                            : $record->name
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->label('Submitted by')
                    ->relationship('uploader', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id())
                    ->required(),
                TextInput::make('youtube_url')
                    ->label('YouTube URL')
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->helperText('Paste a YouTube watch, share, shorts, or embed URL.')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->maxLength(120)
                    ->columnSpanFull(),
                Toggle::make('is_approved')
                    ->label('Approved')
                    ->helperText('Approved videos appear in the public venue carousel.')
                    ->default(true),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }
}
