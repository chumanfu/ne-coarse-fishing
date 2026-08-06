<?php

namespace App\Filament\Resources\MessageThreads\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MessageThreadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Angler')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Message a registered angler.'),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(160),
                Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->rows(8)
                    ->columnSpanFull(),
            ]);
    }
}
