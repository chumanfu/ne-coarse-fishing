<?php

namespace App\Filament\Resources\Venues\Tables;

use App\Models\Venue;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VenuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('ticket_type')->badge(),
                TextColumn::make('clubs_count')->counts('clubs')->label('Clubs'),
                TextColumn::make('clubs.name')
                    ->label('Owned by')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('contact_email')
                    ->label('Contact email')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')->label('Submitted by'),
                TextColumn::make('manager.name')->label('Manager')->placeholder('—'),
                TextColumn::make('invite_sent_at')
                    ->label('Invite sent')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_approved')->boolean()->label('Approved'),
                IconColumn::make('manager_verified')->boolean()->label('Verified'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')->label('Approved'),
                TernaryFilter::make('invite_sent_at')
                    ->label('Invite sent')
                    ->nullable(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->visible(fn ($record) => ! $record->is_approved)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['is_approved' => true])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
