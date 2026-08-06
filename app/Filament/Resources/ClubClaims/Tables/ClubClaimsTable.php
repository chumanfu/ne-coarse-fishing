<?php

namespace App\Filament\Resources\ClubClaims\Tables;

use App\Models\ClubClaim;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClubClaimsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('club.name')->searchable()->sortable(),
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
                    ->visible(fn (ClubClaim $record) => $record->status === 'pending')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (ClubClaim $record): void {
                        $record->update(['status' => 'approved']);
                        $record->club->update([
                            'manager_id' => $record->user_id,
                            'manager_verified' => true,
                        ]);
                        $record->user->assignRole('fishery_manager');
                    }),
                Action::make('reject')
                    ->visible(fn (ClubClaim $record) => $record->status === 'pending')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (ClubClaim $record) => $record->update(['status' => 'rejected'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
