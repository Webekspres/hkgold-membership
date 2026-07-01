<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\Schemas;

use App\Models\CategoryReward;
use App\Models\Reward;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class RewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Reward')
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->options(fn (): array => CategoryReward::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                       

                        TextInput::make('name')
                            ->label('Nama reward')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(50)
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper($state) : $state)
                            ->rules(fn (?Reward $record): array => [
                                'required',
                                'string',
                                'max:50',
                                Rule::unique('rewards', 'sku')->ignore($record),
                            ])
                            ->validationMessages([
                                'required' => 'SKU wajib diisi.',
                                'unique' => 'SKU sudah digunakan.',
                            ]),

                        TextInput::make('points_required')
                            ->label('Poin dibutuhkan')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->numeric()
                            ->validationMessages([
                                'required' => 'Poin dibutuhkan wajib diisi.',
                                'min' => 'Poin minimal 1.',
                            ]),

                        DateTimePicker::make('start_at')
                            ->label('Mulai berlaku')
                            ->required()
                            ->native(false),

                        DateTimePicker::make('end_at')
                            ->label('Berakhir')
                            ->required()
                            ->after('start_at')
                            ->native(false),

                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->columnSpanFull(),

                            Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Gambar Reward')
                    ->schema([
                        Repeater::make('reward_images')
                            ->label('Gambar')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Gambar')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['16:9'])
                                    ->imageAspectRatio('16:9')
                                    ->automaticallyCropImagesToAspectRatio()
                                    ->disk(fn ($livewire): string => filled($livewire->getRecord()?->getKey()) ? 'r2' : 'content_staging')
                                    ->directory('temp')
                                    ->maxSize(2048)
                                    ->required(),
                            ])
                            ->reorderable()
                            ->addActionLabel('Tambah gambar')
                            ->maxItems(10)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
