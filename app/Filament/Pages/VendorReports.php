<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Pages\Page;

class VendorReports extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $title           = 'My Reports';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort     = 10;
    protected static string $view             = 'filament.pages.vendor-reports';

    public string $period = '6months';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(RolesEnum::Vendor->value) ?? false;
    }

    private function vendorId(): int
    {
        return auth()->id();
    }

    private function activeStatuses(): array
    {
        return [
            OrderStatusEnum::Paid->value,
            OrderStatusEnum::Processing->value,
            OrderStatusEnum::Shipped->value,
            OrderStatusEnum::Delivered->value,
            OrderStatusEnum::Completed->value,
        ];
    }

    private function dateFrom(): ?Carbon
    {
        return match ($this->period) {
            '7days'   => now()->subDays(7),
            '30days'  => now()->subDays(30),
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            '12months'=> now()->subMonths(12),
            default   => null,
        };
    }

    private function baseQuery()
    {
        $q = Order::where('vendor_user_id', $this->vendorId())
            ->whereIn('status', $this->activeStatuses());
        if ($from = $this->dateFrom()) {
            $q->where('created_at', '>=', $from);
        }
        return $q;
    }

    public function getMonthlyBreakdown(): array
    {
        return $this->baseQuery()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month,
                         COUNT(*) as orders,
                         SUM(total_price) as revenue,
                         SUM(vendor_subtotal) as earnings")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->get()
            ->toArray();
    }

    public function getOrdersByStatus(): array
    {
        $from = $this->dateFrom();
        return Order::where('vendor_user_id', $this->vendorId())
            ->selectRaw('status, COUNT(*) as total')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    public function getTopProducts(): array
    {
        $from = $this->dateFrom();
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.vendor_user_id', $this->vendorId())
            ->whereIn('orders.status', $this->activeStatuses())
            ->when($from, fn ($q) => $q->where('orders.created_at', '>=', $from))
            ->selectRaw('products.title, SUM(order_items.quantity) as qty, SUM(order_items.price * order_items.quantity) as revenue')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getChartData(): array
    {
        $rows = $this->getMonthlyBreakdown();
        return [
            'labels'   => array_column($rows, 'month'),
            'earnings' => array_map(fn ($r) => round((float) $r['earnings'], 2), $rows),
        ];
    }

    public function getSummary(): array
    {
        $q = $this->baseQuery();
        return [
            'orders'   => (clone $q)->count(),
            'revenue'  => (clone $q)->sum('total_price'),
            'earnings' => (clone $q)->sum('vendor_subtotal'),
        ];
    }

    public function updatedPeriod(): void
    {
        $this->dispatch('update-vendor-chart', chartData: $this->getChartData());
    }
}
