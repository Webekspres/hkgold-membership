<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Schemas;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                View::make('filament.resources.contents.partials.auto-save-poller')
                    ->columnSpanFull(),
                Section::make('Informasi Konten')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(self::statusOptions())
                            ->default(ContentStatus::Draft->value)
                            ->required()
                            ->native(false),
                        Select::make('type')
                            ->label('Tipe')
                            ->options(self::typeOptions())
                            ->default(ContentType::News->value)
                            ->required()
                            ->native(false)
                            ->live()
                            ->columnSpan(fn (Get $get): int => $get('type') === ContentType::Event->value ? 1 : 2),
                        DateTimePicker::make('event_date')
                            ->label('Tanggal acara')
                            ->visible(fn (Get $get): bool => $get('type') === ContentType::Event->value)
                            ->native(false),
                        Textarea::make('location_address')
                            ->label('Alamat lokasi')
                            ->rows(2)
                            ->visible(fn (Get $get): bool => $get('type') === ContentType::Event->value)
                            ->columnSpanFull(),
                        TextInput::make('location_url')
                            ->label('Link maps')
                            ->url()
                            ->maxLength(500)
                            ->visible(fn (Get $get): bool => $get('type') === ContentType::Event->value)
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),
                        RichEditor::make('body_content')
                            ->label('Konten')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Gambar Cover')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('cover_images')
                            ->label('Gambar Cover')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9'])
                            ->imageAspectRatio('16:9')
                            ->automaticallyCropImagesToAspectRatio()
                            ->automaticallyResizeImagesToWidth('1200')
                            ->automaticallyResizeImagesToHeight('675')
                            ->automaticallyUpscaleImagesWhenResizing(false)
                            ->multiple()
                            ->reorderable()
                            ->disk(fn ($livewire): string => filled($livewire->getRecord()?->getKey()) ? 'r2' : 'content_staging')
                            ->directory('temp')
                            ->maxSize(512)
                            ->maxFiles(10)
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

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            ContentStatus::Draft->value => 'Draft',
            ContentStatus::Archived->value => 'Archived',
            ContentStatus::Published->value => 'Published',
        ];
    }
}
