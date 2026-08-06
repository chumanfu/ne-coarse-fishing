<?php

namespace App\Filament\Resources\MessageThreads\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class MessageThreadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->options(fn (): array => User::query()
                        ->orderBy('name')
                        ->get(['id', 'name', 'email'])
                        ->mapWithKeys(fn (User $user) => [
                            $user->id => $user->name.' ('.$user->email.')',
                        ])
                        ->all())
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        return User::query()
                            ->where(function (Builder $query) use ($search): void {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orderBy('name')
                            ->limit(50)
                            ->get(['id', 'name', 'email'])
                            ->mapWithKeys(fn (User $user) => [
                                $user->id => $user->name.' ('.$user->email.')',
                            ])
                            ->all();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $user = User::query()->find($value);

                        return $user ? $user->name.' ('.$user->email.')' : null;
                    })
                    ->required()
                    ->helperText('Any registered user can be messaged — search by name or email.'),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(160),
                Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->rows(8)
                    ->columnSpanFull(),
            ]);
    }
}
