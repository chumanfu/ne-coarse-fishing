<?php

namespace App\Filament\Resources\Species\Schemas;

use App\Models\Species;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SpeciesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('slug')->maxLength(255),
                Select::make('type')
                    ->options(Species::TYPES)
                    ->searchable()
                    ->nullable(),
                CheckboxList::make('habitats')
                    ->options(Species::HABITATS)
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
