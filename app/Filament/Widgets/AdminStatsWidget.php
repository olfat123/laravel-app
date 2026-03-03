<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Order;
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->hasRole(RolesEnum::Admin->value) ?? false;
    }

    protected function getStats(): array
    {
        $activeStatuses = [
            OrderStatusEnum::Paid->value,
            OrderStatusEnum::Processing->value,
            OrderStatusEnum::Shipped->value,
            OrderStatusEnum::Delivered->value,
            OrderStatusEnum::Completed->value,
        ];

        $totalRevenue = Order::whereIn('status', $activeStatuses)
            ->sum('total_price');

        $totalWebsiteCommission = Order::whereIn('status', $activeStatuses)
            ->sum('website_commission');

        $totalPayableToVendors = Order::whereIn('status', $activeStatuses)
            ->sum('vendor_subtotal');

        $totalOrders = Order::whereIn('status', $activeStatuses)->count();
        $commissionRate = (float) Setting::get('website_commission', 0);

        return [
            Stat::make('Total Revenue', number_format($totalRevenue, 2) . ' EGP')
                ->description('From ' . $totalOrders . ' fulfilled orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Website Commission Earned', number_format($totalWebsiteCommission, 2) . ' EGP')
                ->description('At ' . $commissionRate . '% — from fulfilled orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Total Payable to Vendors', number_format($totalPayableToVendors, 2) . ' EGP')
                ->description('Vendor subtotals to be transferred')
                ->descriptionIcon('heroicon-m-arrow-up-right')
                ->color('primary'),
        ];
    }
}
