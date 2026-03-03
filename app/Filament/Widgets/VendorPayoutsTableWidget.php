<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class VendorPayoutsTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Vendor Earnings & Payouts';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(RolesEnum::Admin->value) ?? false;
    }

    public function table(Table $table): Table
    {
        $fulfilledStatuses = [
            OrderStatusEnum::Paid->value,
            OrderStatusEnum::Processing->value,
            OrderStatusEnum::Shipped->value,
            OrderStatusEnum::Delivered->value,
            OrderStatusEnum::Completed->value,
        ];

        $pendingStatuses = [
            OrderStatusEnum::Paid->value,
            OrderStatusEnum::Processing->value,
            OrderStatusEnum::Shipped->value,
            OrderStatusEnum::Delivered->value,
        ];

        return $table
            ->query(
                User::role(RolesEnum::Vendor->value)
                    ->with('vendor')
                    ->withCount([
                        'vendorOrders as total_orders' => fn (Builder $q) => $q->whereIn('status', $fulfilledStatuses),
                    ])
                    ->withSum(
                        ['vendorOrders as total_earnings' => fn (Builder $q) => $q->whereIn('status', $fulfilledStatuses)],
                        'vendor_subtotal'
                    )
                    ->withSum(
                        ['vendorOrders as pending_payout' => fn (Builder $q) => $q->whereIn('status', $pendingStatuses)],
                        'vendor_subtotal'
                    )
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor.store_name')
                    ->label('Store')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Orders')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_earnings')
                    ->label('Total Earnings')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2) . ' EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pending_payout')
                    ->label('Amount to Send')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2) . ' EGP')
                    ->sortable()
                    ->color('warning'),
            ])
            ->defaultSort('pending_payout', 'desc');
    }
}
