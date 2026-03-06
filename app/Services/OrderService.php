<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(protected CartService $cartService) {}

    /**
     * Place orders for the whole cart or a single vendor's slice.
     * Handles coupon, tax calculation, commission, and DB transaction.
     *
     * @param  array       $data      Validated shipping + payment fields
     * @param  int|null    $vendorId  Limit checkout to one vendor (null = all)
     * @return Order[]
     *
     * @throws \InvalidArgumentException  On coupon errors
     * @throws \RuntimeException          On empty cart
     */
    public function placeOrders(array $data, ?int $vendorId = null): array
    {
        $coupon       = $this->resolveCoupon($data['coupon_code'] ?? null);
        $checkoutItems = $this->resolveCheckoutItems($vendorId);

        $commissionRate   = (float) Setting::get('website_commission', 0);
        $taxRate          = (float) Setting::get('tax_rate', 0);
        $pricesIncludeTax = Setting::get('prices_include_tax', '0') === '1';

        $checkoutTotal = collect($checkoutItems)->sum('total_price');

        $totalDiscount = 0;
        if ($coupon) {
            if ($error = $coupon->validate((float) $checkoutTotal)) {
                throw new \InvalidArgumentException($error);
            }
            $totalDiscount = $coupon->calculateDiscount((float) $checkoutTotal);
        }

        DB::beginTransaction();
        try {
            $orders = [];

            foreach ($checkoutItems as $item) {
                $orders[] = $this->createOrder(
                    $item,
                    $data,
                    $coupon,
                    $checkoutTotal,
                    $totalDiscount,
                    $taxRate,
                    $pricesIncludeTax,
                    $commissionRate
                );
            }

            $coupon?->incrementUsage();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $orders;
    }

    // ──────────────────────────────────────────────────────────────────────

    private function resolveCoupon(?string $code): ?Coupon
    {
        if (empty($code)) {
            return null;
        }

        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            throw new \InvalidArgumentException('Invalid coupon code.');
        }

        return $coupon;
    }

    private function resolveCheckoutItems(?int $vendorId): array
    {
        $all = $this->cartService->getCartItemsGrouped();

        $items = $vendorId
            ? (isset($all[$vendorId]) ? [$vendorId => $all[$vendorId]] : [])
            : $all;

        if (empty($items)) {
            throw new \RuntimeException('Your cart is empty.');
        }

        return $items;
    }

    private function createOrder(
        array    $item,
        array    $data,
        ?Coupon  $coupon,
        float    $checkoutTotal,
        float    $totalDiscount,
        float    $taxRate,
        bool     $pricesIncludeTax,
        float    $commissionRate
    ): Order {
        $totalPrice = (float) $item['total_price'];

        // Distribute discount proportionally across vendors
        $vendorDiscount  = $checkoutTotal > 0
            ? round($totalDiscount * ($totalPrice / $checkoutTotal), 4)
            : 0;
        $discountedTotal = max(0.0, $totalPrice - $vendorDiscount);

        // Calculate tax
        $taxAmount = 0.0;
        if ($taxRate > 0) {
            if ($pricesIncludeTax) {
                $taxAmount = round($discountedTotal - $discountedTotal / (1 + $taxRate / 100), 4);
            } else {
                $taxAmount       = round($discountedTotal * $taxRate / 100, 4);
                $discountedTotal = round($discountedTotal + $taxAmount, 4);
            }
        }

        $websiteCommission = round($discountedTotal * $commissionRate / 100, 4);
        $vendorSubtotal    = round($discountedTotal - $websiteCommission, 4);

        $order = Order::create([
            'user_id'            => auth()->id(),
            'vendor_user_id'     => $item['user']['id'],
            'total_price'        => $discountedTotal,
            'discount_amount'    => $vendorDiscount,
            'tax_rate'           => $taxRate,
            'tax_amount'         => $taxAmount,
            'coupon_code'        => $coupon?->code,
            'website_commission' => $websiteCommission,
            'vendor_subtotal'    => $vendorSubtotal,
            'status'             => OrderStatusEnum::Pending->value,
            'payment_method'     => $data['payment_method'],
            'shipping_name'      => $data['shipping_name'],
            'shipping_phone'     => $data['shipping_phone'],
            'shipping_address'   => $data['shipping_address'],
            'shipping_city'      => $data['shipping_city'],
            'shipping_state'     => $data['shipping_state'] ?? null,
            'shipping_country'   => $data['shipping_country'],
            'shipping_zip'       => $data['shipping_zip'] ?? null,
        ]);

        foreach ($item['items'] as $cartItem) {
            $order->items()->create([
                'product_id'                => $cartItem['product_id'],
                'quantity'                  => $cartItem['quantity'],
                'price'                     => $cartItem['price'],
                'variation_type_option_ids' => $cartItem['option_ids'] ?? null,
            ]);
        }

        return $order;
    }
}
