<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contents\Schemas;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Filament\Resources\Contents\Support\ContentFormSupport;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                                    ->disk('r2')
                                    ->directory('contents')
                                    ->image()
                                    ->imageEditor()
                                    ->imageAspectRatio('4:3')
                                    ->automaticallyOpenImageEditorForAspectRatio()
                                    ->imageEditorAspectRatioOptions(['4:3'])
                                    ->imageEditorViewportWidth(1200)
                                    ->imageEditorViewportHeight(900)
                                    ->maxSize(300)
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
