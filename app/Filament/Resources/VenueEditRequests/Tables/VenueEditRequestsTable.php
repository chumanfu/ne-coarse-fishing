<?php

namespace App\Filament\Resources\VenueEditRequests\Tables;

use App\Models\VenueEditRequest;
use App\Services\VenuePersistenceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VenueEditRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = auth()->user();

                if ($user?->hasRole('super_admin')) {
                    return $query;
                }

                return $query->whereHas('venue', function (Builder $inner) use ($user): void {
                    $inner->where('manager_id', $user?->id);

                    if ($user?->hasRole('club_owner')) {
                        $inner->orWhereHas('clubs', fn (Builder $clubs) => $clubs->where('manager_id', $user->id));
                    }
                });
            })
            ->columns([
                TextColumn::make('venue.name')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Submitted by')->searchable(),
                TextColumn::make('message')->limit(40)->wrap()->placeholder('—'),
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
                ViewAction::make(),
                Action::make('approve')
                    ->visible(fn (VenueEditRequest $record): bool => auth()->user()?->can('review', $record) ?? false)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (VenueEditRequest $record): void {
                        app(VenuePersistenceService::class)->apply($record->venue, $record->proposed_data);
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                    }),
                Action::make('reject')
                    ->visible(fn (VenueEditRequest $record): bool => auth()->user()?->can('review', $record) ?? false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (VenueEditRequest $record) => $record->update([
                        'status' => 'rejected',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
