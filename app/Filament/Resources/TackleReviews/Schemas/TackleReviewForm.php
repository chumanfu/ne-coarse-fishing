<?php

namespace App\Filament\Resources\TackleReviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TackleReviewForm
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
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('brand')
                    ->maxLength(255),
                TextInput::make('rating')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(5)
                    ->default(0)
                    ->helperText('0–5 stars (zero allowed).'),
                Textarea::make('body')
                    ->required()
                    ->rows(8)
                    ->columnSpanFull(),
                TextInput::make('purchase_url')
                    ->url()
                    ->maxLength(2048)
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),
                Toggle::make('featured_on_home')
                    ->label('Feature on home page')
                    ->default(false),
            ]);
    }
}
