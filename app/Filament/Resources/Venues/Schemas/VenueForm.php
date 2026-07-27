<?php

namespace App\Filament\Resources\Venues\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Venue')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('slug')->maxLength(255),
                        Select::make('user_id')
                            ->relationship('creator', 'name')
                            ->searchable()
                            ->required()
                            ->label('Submitted by'),
                        Select::make('manager_id')
                            ->relationship('manager', 'name')
                            ->searchable()
                            ->label('Fishery manager'),
                        Textarea::make('overview')->rows(4)->columnSpanFull(),
                        TextInput::make('latitude')->numeric()->required(),
                        TextInput::make('longitude')->numeric()->required(),
                        TextInput::make('address')->maxLength(255)->columnSpanFull(),
                        TextInput::make('url')
                            ->label('Website URL')
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        TextInput::make('what3words')
                            ->label('what3words')
                            ->helperText('Three words separated by dots, e.g. filled.count.soap')
                            ->maxLength(64)
                            ->columnSpanFull(),
                        Textarea::make('directions')->rows(3)->columnSpanFull(),
                        Select::make('ticket_type')
                            ->options([
                                'day_ticket' => 'Day Ticket',
                                'club' => 'Club',
                                'syndicate' => 'Syndicate',
                                'mixed' => 'Mixed',
                            ])
                            ->required(),
                        Textarea::make('day_ticket_info')->rows(3),
                        Textarea::make('membership_info')->rows(3),
                        Textarea::make('opening_times')->rows(2),
                        Textarea::make('season_info')->rows(2),
                        Textarea::make('tactics_guide')->rows(4)->columnSpanFull(),
                        Toggle::make('is_complex'),
                        Toggle::make('is_approved')->label('Approved for public listing'),
                        Toggle::make('manager_verified')->label('Manager verified badge'),
                    ])
                    ->columns(2),
            ]);
    }
}
