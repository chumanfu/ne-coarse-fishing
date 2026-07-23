<?php

namespace App\Filament\Resources\FishingSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FishingSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable(),
                TextColumn::make('venue.name')->searchable(),
                TextColumn::make('water.name')->placeholder('—'),
                TextColumn::make('fished_at')->date()->sortable(),
                TextColumn::make('weather')->toggleable(),
            ])
            ->defaultSort('fished_at', 'desc')
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
