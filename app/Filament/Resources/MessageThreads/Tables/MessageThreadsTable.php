<?php

namespace App\Filament\Resources\MessageThreads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessageThreadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('unread')
                    ->label('')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isUnreadForAdmin())
                    ->trueIcon('heroicon-s-envelope')
                    ->falseIcon('heroicon-o-envelope-open')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('contact_name')
                    ->label('From')
                    ->description(fn ($record) => $record->contact_email)
                    ->searchable(['contact_name', 'contact_email']),
                TextColumn::make('source')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'admin' ? 'Admin' : 'Contact form'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'open' ? 'success' : 'gray'),
                TextColumn::make('last_message_at')
                    ->label('Latest')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('source')
                    ->options([
                        'contact' => 'Contact form',
                        'admin' => 'Admin',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
