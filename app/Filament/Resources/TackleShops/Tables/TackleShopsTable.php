<?php

namespace App\Filament\Resources\TackleShops\Tables;

use App\Models\TackleShop;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TackleShopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->getStateUsing(fn (TackleShop $record): ?string => $record->logoUrl())
                    ->height(40)
                    ->defaultImageUrl(null),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('location_type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => TackleShop::LOCATION_TYPES[$state] ?? (string) $state)
                    ->sortable(),
                TextColumn::make('town')->placeholder('—')->searchable()->toggleable(),
                TextColumn::make('url')
                    ->label('Website')
                    ->url(fn (TackleShop $record): string => $record->url, shouldOpenInNewTab: true)
                    ->limit(30)
                    ->searchable(),
                IconColumn::make('is_featured')->boolean()->label('Home'),
                IconColumn::make('is_published')->boolean()->label('Live'),
                TextColumn::make('sort_order')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('location_type')->options(TackleShop::LOCATION_TYPES),
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
