<?php

namespace App\Filament\Resources\TackleShops\Schemas;

use App\Models\TackleShop;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TackleShopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('slug')->maxLength(255),
                TextInput::make('url')
                    ->label('Website URL')
                    ->url()
                    ->required()
                    ->maxLength(255),
                FileUpload::make('logo_path')
                    ->label('Logo')
                    ->image()
                    ->disk('public')
                    ->directory('tackle-shop-logos')
                    ->visibility('public')
                    ->imagePreviewHeight('120')
                    ->columnSpanFull(),
                Select::make('location_type')
                    ->options(TackleShop::LOCATION_TYPES)
                    ->required()
                    ->native(false),
                TextInput::make('town')->maxLength(255),
                TextInput::make('address')->maxLength(255)->columnSpanFull(),
                TextInput::make('latitude')->numeric()->step(0.0000001),
                TextInput::make('longitude')->numeric()->step(0.0000001),
                TextInput::make('phone')->tel()->maxLength(50),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
                Textarea::make('overview')->rows(4)->columnSpanFull(),
                Toggle::make('is_featured')->label('Featured on home page'),
                Toggle::make('is_published')->label('Published')->default(true),
            ]);
    }
}
