<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryRewards\Schemas;

use App\Filament\Resources\CategoryRewards\Support\CategoryRewardFormSupport;
use App\Models\CategoryReward;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryRewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Nama kategori')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull()
                    ->live(onBlur: true)
                    ->rules(fn (?CategoryReward $record): array => CategoryRewardFormSupport::nameValidationRules($record))
                    ->validationMessages([
                        'required' => 'Nama kategori wajib diisi.',
                        'max' => 'Nama kategori maksimal 100 karakter.',
                    ]),
            ]);
    }
}
