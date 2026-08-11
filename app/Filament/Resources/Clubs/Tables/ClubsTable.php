<?php

namespace App\Filament\Resources\Clubs\Tables;

use App\Models\Club;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ClubsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->getStateUsing(fn (Club $record): ?string => $record->logoUrl())
                    ->height(40),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('town')->placeholder('—')->searchable()->toggleable(),
                TextColumn::make('contact_email')
                    ->label('Contact email')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('url')
                    ->label('Website')
                    ->url(fn (Club $record): ?string => $record->url, shouldOpenInNewTab: true)
                    ->limit(30)
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('members_count')->counts('members')->label('Members'),
                TextColumn::make('invite_sent_at')
                    ->label('Invite sent')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_featured')->boolean()->label('Home'),
                IconColumn::make('is_published')->boolean()->label('Live'),
                TextColumn::make('sort_order')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('invite_sent_at')
                    ->label('Invite sent')
                    ->nullable(),
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
