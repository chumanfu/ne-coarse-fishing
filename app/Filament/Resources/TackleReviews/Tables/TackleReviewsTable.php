<?php

namespace App\Filament\Resources\TackleReviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TackleReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('rating')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                IconColumn::make('featured_on_home')
                    ->boolean()
                    ->label('Home'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
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
