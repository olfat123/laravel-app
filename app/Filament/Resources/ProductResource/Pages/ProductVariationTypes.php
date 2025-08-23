<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Dom\Text;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use App\Enums\ProductVariationTypeEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;


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
                        TextInput::make('name')
                            ->label(__('Variation Type Name'))
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->options(ProductVariationTypeEnum::labels())
                            ->required(),
                        Repeater::make('options')
                            ->relationship()
                            ->collapsible()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Option Name'))
                                    ->required()
                                    ->columnSpan(2)
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
                                    ->maxFiles(1),
                            ])
                            ->columnSpan(2)
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
