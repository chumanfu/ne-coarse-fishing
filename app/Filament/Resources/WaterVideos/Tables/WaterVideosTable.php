<?php

namespace App\Filament\Resources\WaterVideos\Tables;

use App\Models\WaterVideo;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WaterVideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumb')
                    ->getStateUsing(fn (WaterVideo $record): string => $record->thumbnailUrl())
                    ->height(48)
                    ->width(86),
                TextColumn::make('title')
                    ->searchable()
                    ->placeholder('Untitled')
                    ->description(fn (WaterVideo $record): string => $record->youtube_id),
                TextColumn::make('water.name')
                    ->label('Water')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('water.venue.name')
                    ->label('Venue')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('uploader.name')
                    ->label('Submitted by')
                    ->placeholder('—'),
                IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved'),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')->label('Approved'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->visible(fn (WaterVideo $record): bool => ! $record->is_approved)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (WaterVideo $record) => $record->markApproved(auth()->user())),
                Action::make('open')
                    ->label('YouTube')
                    ->url(fn (WaterVideo $record): string => $record->watchUrl(), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
