<?php

namespace App\Filament\Resources\VenueClaims\Tables;

use App\Models\VenueClaim;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VenueClaimsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('venue.name')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Claimant')->searchable(),
                TextColumn::make('message')->limit(40)->wrap(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->visible(fn (VenueClaim $record) => $record->status === 'pending')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (VenueClaim $record): void {
                        $record->update(['status' => 'approved']);
                        $record->venue->update([
                            'manager_id' => $record->user_id,
                            'manager_verified' => true,
                        ]);
                        $record->user->assignRole('fishery_manager');
                    }),
                Action::make('reject')
                    ->visible(fn (VenueClaim $record) => $record->status === 'pending')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (VenueClaim $record) => $record->update(['status' => 'rejected'])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
