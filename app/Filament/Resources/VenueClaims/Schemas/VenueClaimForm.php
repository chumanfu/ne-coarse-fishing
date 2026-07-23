<?php

namespace App\Filament\Resources\VenueClaims\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VenueClaimForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('venue_id')->relationship('venue', 'name')->searchable()->required(),
                Select::make('user_id')->relationship('user', 'name')->searchable()->required(),
                Textarea::make('message')->rows(4)->columnSpanFull(),
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
