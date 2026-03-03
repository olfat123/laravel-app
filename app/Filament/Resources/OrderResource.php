<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 2;

    /**
     * Admins see all orders; vendors see only orders assigned to them.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['user', 'vendor', 'items.product']);

        $user = Filament::auth()->user();

        if ($user->hasRole(RolesEnum::Vendor)) {
            $query->where('vendor_user_id', $user->id);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();
        return $user->hasRole(RolesEnum::Admin) || $user->hasRole(RolesEnum::Vendor);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return Filament::auth()->user()->hasRole(RolesEnum::Admin);
    }

    public static function form(Form $form): Form
    {
        $isAdmin = Filament::auth()->user()->hasRole(RolesEnum::Admin);

        return $form
            ->schema([
                Forms\Components\Section::make('Order Status')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(OrderStatusEnum::labels())
                            ->required()
                            ->disabled(! $isAdmin && ! Filament::auth()->user()->hasRole(RolesEnum::Vendor)),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->disabled(! $isAdmin),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order Details')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Order #'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending'    => 'gray',
                                'processing' => 'warning',
                                'paid'       => 'success',
                                'shipped'    => 'info',
                                'completed'  => 'success',
                                'delivered'  => 'success',
                                'cancelled'  => 'danger',
                                'failed'     => 'danger',
                                'refunded'   => 'warning',
                                default      => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Payment'),
                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Total')
                            ->money(config('app.currency', 'USD')),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Customer'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Placed At')
                            ->dateTime(),
                    ]),

                Infolists\Components\Section::make('Shipping Address')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('shipping_name')->label('Name'),
                        Infolists\Components\TextEntry::make('shipping_phone')->label('Phone'),
                        Infolists\Components\TextEntry::make('shipping_address')->label('Address'),
                        Infolists\Components\TextEntry::make('shipping_city')->label('City'),
                        Infolists\Components\TextEntry::make('shipping_state')->label('State'),
                        Infolists\Components\TextEntry::make('shipping_country')->label('Country'),
                        Infolists\Components\TextEntry::make('shipping_zip')->label('ZIP'),
                    ]),

                Infolists\Components\Section::make('Update Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('—'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => Filament::auth()->user()->hasRole(RolesEnum::Admin)),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money(config('app.currency', 'USD'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'gray',
                        'processing' => 'warning',
                        'paid'       => 'success',
                        'shipped'    => 'info',
                        'completed', 'delivered' => 'success',
                        'cancelled', 'failed'    => 'danger',
                        'refunded'   => 'warning',
                        default      => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'cod'       => 'Cash on Delivery',
                        'paymob_cc' => 'Credit Card',
                        default     => $state ?? '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'cod'       => 'success',
                        'paymob_cc' => 'info',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatusEnum::labels()),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cod'       => 'Cash on Delivery',
                        'paymob_cc' => 'Credit Card',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('updateStatus')
                    ->label('Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(OrderStatusEnum::labels())
                            ->required()
                            ->default(fn (Order $record) => $record->status),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->default(fn (Order $record) => $record->notes),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                            'notes'  => $data['notes'] ?? $record->notes,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markProcessing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-arrow-path')
                        ->action(fn ($records) => $records->each->update(['status' => OrderStatusEnum::Processing->value]))
                        ->requiresConfirmation()
                        ->visible(fn () => Filament::auth()->user()->hasRole(RolesEnum::Admin)),
                    Tables\Actions\BulkAction::make('markShipped')
                        ->label('Mark as Shipped')
                        ->icon('heroicon-o-truck')
                        ->action(fn ($records) => $records->each->update(['status' => OrderStatusEnum::Shipped->value]))
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'view'   => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
