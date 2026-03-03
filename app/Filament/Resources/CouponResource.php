<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Coupon;
use App\Enums\RolesEnum;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\CouponResource\Pages;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasRole(RolesEnum::Admin->value) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Coupon Code')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('SUMMER25')
                    ->afterStateUpdated(fn ($state, callable $set) => $set('code', strtoupper($state)))
                    ->live(onBlur: true),

                Forms\Components\Select::make('type')
                    ->label('Discount Type')
                    ->options([
                        'percentage' => 'Percentage (%)',
                        'fixed'      => 'Fixed Amount',
                    ])
                    ->required()
                    ->default('percentage'),

                Forms\Components\TextInput::make('value')
                    ->label('Discount Value')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->helperText('Enter a percentage (e.g. 10 for 10%) or a fixed amount.'),

                Forms\Components\TextInput::make('min_order_amount')
                    ->label('Minimum Order Amount')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->nullable()
                    ->placeholder('Leave blank for no minimum'),

                Forms\Components\TextInput::make('max_uses')
                    ->label('Max Uses')
                    ->numeric()
                    ->minValue(1)
                    ->integer()
                    ->nullable()
                    ->placeholder('Leave blank for unlimited'),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expiry Date')
                    ->nullable()
                    ->placeholder('Leave blank for no expiry'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed'      => 'info',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn ($state, Coupon $record) =>
                        $record->type === 'percentage' ? "{$state}%" : number_format((float)$state, 2) . ' EGP'
                    ),

                Tables\Columns\TextColumn::make('min_order_amount')
                    ->label('Min Order')
                    ->numeric(2)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Used')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_uses')
                    ->label('Max Uses')
                    ->placeholder('∞'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, Y')
                    ->placeholder('Never')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
