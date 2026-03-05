<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Models\Attribute;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use App\Enums\ProductVariationTypeEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;


class ProductVariationTypes extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Variation Types';
    protected static ?string $navigationIcon = 'heroicon-m-numbered-list';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('variationTypes')
                    ->label(false)
                    ->relationship()
                    ->collapsible()
                    ->defaultItems(1)
                    ->addActionLabel(__('Add new variation type'))
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        // ── Library picker ────────────────────────────────
                        Select::make('attribute_library_id')
                            ->label('Load from attribute library (optional)')
                            ->options(fn () => Attribute::orderBy('name')->pluck('name', 'id'))
                            ->placeholder('— choose an attribute to auto-fill —')
                            ->live()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) return;
                                $attribute = Attribute::with('options')->find($state);
                                if (!$attribute) return;
                                $set('name', $attribute->name);
                                $set('name_ar', $attribute->name_ar ?? '');
                                $set('options', $attribute->options->map(fn ($opt) => [
                                    'name'    => $opt->name,
                                    'name_ar' => $opt->name_ar ?? '',
                                ])->toArray());
                            }),

                        // ── Names ─────────────────────────────────────────
                        TextInput::make('name')
                            ->label('Variation Type Name (English)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ar')
                            ->label('Variation Type Name (Arabic — بالعربي)')
                            ->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                            ->maxLength(255),

                        Select::make('type')
                            ->options(ProductVariationTypeEnum::labels())
                            ->required(),

                        // ── Options repeater ──────────────────────────────
                        Repeater::make('options')
                            ->relationship()
                            ->collapsible()
                            ->columnSpan(2)
                            ->addActionLabel('Add option')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Option Name (English)')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('name_ar')
                                    ->label('Option Name (Arabic — بالعربي)')
                                    ->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                                    ->maxLength(255),
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->collection('variation_type_options')
                                    ->label(__('Option Image'))
                                    ->image()
                                    ->multiple()
                                    ->openable()
                                    ->reorderable()
                                    ->appendFiles()
                                    ->collection('images')
                                    ->panelLayout('grid')
                                    ->preserveFilenames()
                                    ->maxFiles(10)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

