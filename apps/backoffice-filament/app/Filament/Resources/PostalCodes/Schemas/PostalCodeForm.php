<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostalCodes\Schemas;

use App\Models\PostalCode;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class PostalCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Pos')
                    ->required()
                    ->maxLength(20)
                    ->rules(fn (?PostalCode $record): array => [
                        Rule::unique('postal_codes', 'code')->ignore($record?->id),
                    ]),
            ]);
    }
}
