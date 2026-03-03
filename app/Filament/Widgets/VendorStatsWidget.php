<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VendorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(RolesEnum::Vendor->value) ?? false;
    }

    protected function getStats(): array
    {
        $vendorId = auth()->id();

        $fulfilledStatuses = [
            OrderStatusEnum::Paid->value,
            OrderStatusEnum::Processing->value,
            OrderStatusEnum::Shipped->value,
            OrderStatusEnum::Delivered->value,
            OrderStatusEnum::Completed->value,
        ];

        $totalEarnings = Order::where('vendor_user_id', $vendorId)
            ->whereIn('status', $fulfilledStatuses)
            ->sum('vendor_subtotal');

        $pendingPayout = Order::where('vendor_user_id', $vendorId)
            ->whereIn('status', [
                OrderStatusEnum::Paid->value,
                OrderStatusEnum::Processing->value,
                OrderStatusEnum::Shipped->value,
                OrderStatusEnum::Delivered->value,
            ])
            ->sum('vendor_subtotal');

        $completedPayout = Order::where('vendor_user_id', $vendorId)
            ->where('status', OrderStatusEnum::Completed->value)
            ->sum('vendor_subtotal');

        $totalOrders = Order::where('vendor_user_id', $vendorId)
            ->whereIn('status', $fulfilledStatuses)
            ->count();

        return [
            Stat::make('Total Earnings', number_format($totalEarnings, 2) . ' EGP')
                ->description('From ' . $totalOrders . ' fulfilled orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Payout', number_format($pendingPayout, 2) . ' EGP')
                ->description('Amount awaiting transfer to you')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Settled Payouts', number_format($completedPayout, 2) . ' EGP')
                ->description('From completed orders')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
        ];
    }
}
