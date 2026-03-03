<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Order Items';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')
                    ->label('Product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Unit Price')
                    ->money(config('app.currency', 'USD')),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money(config('app.currency', 'USD'))
                    ->state(fn ($record) => $record->price * $record->quantity),
            ])
            ->paginated(false);
    }
}
