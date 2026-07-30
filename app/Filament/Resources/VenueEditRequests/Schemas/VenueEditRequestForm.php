<?php

namespace App\Filament\Resources\VenueEditRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\VenueEditRequest;

class VenueEditRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('venue_id')->relationship('venue', 'name')->searchable()->required()->disabled(),
                Select::make('user_id')->relationship('user', 'name')->searchable()->required()->disabled(),
                Textarea::make('message')->rows(3)->columnSpanFull()->disabled(),
                Textarea::make('proposed_data_preview')
                    ->label('Proposed changes')
                    ->afterStateHydrated(function (Textarea $component, ?VenueEditRequest $record): void {
                        if ($record) {
                            $component->state(json_encode($record->proposed_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        }
                    })
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(16)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
            ]);
    }
}
