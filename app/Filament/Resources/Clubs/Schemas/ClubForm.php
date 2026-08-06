<?php

namespace App\Filament\Resources\Clubs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClubForm
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
                    ->maxLength(255),
                TextInput::make('facebook_url')
                    ->label('Facebook URL')
                    ->url()
                    ->maxLength(2048),
                FileUpload::make('logo_path')
                    ->label('Logo')
                    ->image()
                    ->disk(config('filesystems.uploads'))
                    ->directory('club-logos')
                    ->visibility(config('filesystems.uploads') === 'public' ? 'public' : 'private')
                    ->imagePreviewHeight('120')
                    ->columnSpanFull(),
                TextInput::make('town')->maxLength(255),
                TextInput::make('address')->maxLength(255)->columnSpanFull(),
                TextInput::make('phone')->tel()->maxLength(50),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
                Textarea::make('overview')->rows(4)->columnSpanFull(),
                Toggle::make('is_featured')->label('Featured on home page'),
                Toggle::make('is_published')->label('Published')->default(true),
                Select::make('manager_id')
                    ->label('Owner / manager')
                    ->relationship('manager', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Toggle::make('manager_verified')->label('Manager verified'),
            ]);
    }
}
