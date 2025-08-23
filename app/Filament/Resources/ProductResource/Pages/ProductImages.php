<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;


class ProductImages extends EditRecord
{
    protected static string $resource = ProductResource::class;
    protected static ?string $title = 'Images';
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('images')
                    ->collection('images')
                    ->label(false)
                    ->multiple()
                    ->maxFiles(10)
                    ->image()
                    ->panelLayout('grid')
                    ->appendFiles()
                    ->openable()
                    ->reorderable()
                    ->preserveFilenames()
                    ->columnSpan(2),
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
