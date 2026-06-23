<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryRewards\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryRewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama kategori')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
