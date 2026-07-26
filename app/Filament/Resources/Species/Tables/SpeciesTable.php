<?php

namespace App\Filament\Resources\Species\Tables;

use App\Models\Species;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpeciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Species::TYPES[$state] ?? (string) $state)
                    ->sortable(),
                TextColumn::make('habitats')
                    ->label('Found in')
                    ->formatStateUsing(function ($state): string {
                        $habitats = is_array($state) ? $state : [];

                        return collect($habitats)
                            ->map(fn (string $habitat) => Species::HABITATS[$habitat] ?? ucfirst($habitat))
                            ->join(', ');
                    })
                    ->wrap(),
                TextColumn::make('slug')->searchable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')->options(Species::TYPES),
                SelectFilter::make('habitat')
                    ->label('Habitat')
                    ->options(Species::HABITATS)
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return;
                        }

                        $habitat = $data['value'];
                        $driver = $query->getConnection()->getDriverName();

                        if ($driver === 'sqlite') {
                            $query->where('habitats', 'like', '%"'.$habitat.'"%');
                        } else {
                            $query->whereJsonContains('habitats', $habitat);
                        }
                    }),
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
