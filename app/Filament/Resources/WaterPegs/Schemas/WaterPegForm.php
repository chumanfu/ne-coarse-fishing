<?php

namespace App\Filament\Resources\WaterPegs\Schemas;

use App\Models\Water;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class WaterPegForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('water_id')
                    ->relationship('water', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                        if (filled($get('latitude')) && filled($get('longitude'))) {
                            return;
                        }

                        $water = Water::query()->with('venue')->find($state);
                        $venue = $water?->venue;

                        if ($venue?->latitude !== null && $venue?->longitude !== null) {
                            $set('latitude', round((float) $venue->latitude, 7));
                            $set('longitude', round((float) $venue->longitude, 7));
                        }
                    }),
                Select::make('created_by')
                    ->label('Created by')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')->maxLength(255),
                TextInput::make('number')->maxLength(50),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                View::make('filament.forms.components.location-map')
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->required()
                    ->numeric()
                    ->step(0.0000001)
                    ->default(54.7767),
                TextInput::make('longitude')
                    ->required()
                    ->numeric()
                    ->step(0.0000001)
                    ->default(-1.5753),
                Toggle::make('is_verified')->default(false),
                Select::make('verified_by')
                    ->label('Verified by')
                    ->relationship('verifier', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('verified_at'),
                TextInput::make('sort_order')->required()->numeric()->default(0),
                FileUpload::make('photo_uploads')
                    ->label('Peg photos')
                    ->helperText('Up to 4 images of the peg. Stored on the uploads disk.')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->maxFiles(4)
                    ->maxSize(5120)
                    ->disk(config('filesystems.uploads'))
                    ->directory('peg-photos')
                    ->visibility('public')
                    ->columnSpanFull(),
            ]);
    }
}
