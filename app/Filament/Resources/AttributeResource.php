<?php

namespace App\Filament\Resources;

use App\Enums\RolesEnum;
use App\Models\Attribute;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\AttributeResource\Pages;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->hasRole(RolesEnum::Admin);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Translations')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Attribute Name (English)')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Tabs\Tab::make('Arabic (عربي)')
                            ->schema([
                                TextInput::make('name_ar')
                                    ->label('Attribute Name (Arabic — الاسم بالعربي)')
                                    ->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Options')
                    ->description('Add the possible values for this attribute (e.g. Red, Blue, Small, Large).')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('options')
                            ->relationship()
                            ->label(false)
                            ->addActionLabel('Add Option')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Option (English)')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('name_ar')
                                    ->label('Option (Arabic — بالعربي)')
                                    ->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('Name (Arabic)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options')
                    ->counts('options')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit'   => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
