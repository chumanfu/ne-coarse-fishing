<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Models\Activity;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Activity::typeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Activity::TYPE_VENUE => 'success',
                        Activity::TYPE_SESSION => 'info',
                        Activity::TYPE_TACTIC => 'warning',
                        Activity::TYPE_CLUB => 'primary',
                        Activity::TYPE_TACKLE_SHOP => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->limit(80),
                TextColumn::make('summary')
                    ->placeholder('—')
                    ->searchable()
                    ->wrap()
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(Activity::typeOptions()),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->label('Date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'From '.$data['from'];
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until '.$data['until'];
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Activity $record): string => url($record->url))
                    ->openUrlInNewTab(),
            ])
            ->paginated([25, 50, 100]);
    }
}
