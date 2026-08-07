<?php

namespace App\Filament\Resources\TackleShopClaims\Tables;

use App\Models\TackleShopClaim;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TackleShopClaimsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tackleShop.name')->label('Shop')->searchable()->sortable(),
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
                    ->visible(fn (TackleShopClaim $record) => $record->status === 'pending')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (TackleShopClaim $record): void {
                        $record->update(['status' => 'approved']);
                        $record->tackleShop->update([
                            'manager_id' => $record->user_id,
                            'manager_verified' => true,
                        ]);
                        $record->user->assignRole('tackle_shop_owner');
                    }),
                Action::make('reject')
                    ->visible(fn (TackleShopClaim $record) => $record->status === 'pending')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (TackleShopClaim $record) => $record->update(['status' => 'rejected'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
