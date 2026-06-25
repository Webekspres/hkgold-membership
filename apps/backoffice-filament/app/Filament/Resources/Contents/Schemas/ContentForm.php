<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Schemas;

use App\Enums\ContentType;
use App\Filament\Resources\Contents\Support\ContentFormSupport;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Konten')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Tipe')
                            ->options(self::typeOptions())
                            ->default(ContentType::News->value)
                            ->required()
                            ->native(false)
                            ->live(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', ContentFormSupport::generateSlug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        DateTimePicker::make('event_date')
                            ->label('Tanggal acara')
                            ->visible(fn (Get $get): bool => $get('type') === ContentType::Event->value)
                            ->native(false),
                        RichEditor::make('body_content')
                            ->label('Konten')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Gambar Cover')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('cover_images')
                            ->label('Gambar')
                            ->schema([
                                Hidden::make('media_id'),
                                FileUpload::make('image')
                                    ->label('Gambar cover')
                                    ->image()
                                    ->disk('public')
                                    ->directory('content-covers')
                                    ->visibility('public')
                                    ->maxSize(5_120)
                                    ->required(),
                            ])
                            ->reorderable()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            ContentType::News->value => 'News',
            ContentType::Event->value => 'Event',
        ];
    }
}
