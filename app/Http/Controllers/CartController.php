<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Enums\OrderStatusEnum;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    private function sanitizeImageUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $path = $parts['path'] ?? '';
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . rawurlencode($parts['fragment']) : '';

        return $parts['scheme'] . '://' . $parts['host'] . $port . $encodedPath . $query . $fragment;
    }

    /**
     * Display the cart listing.
     */
    public function index(CartService $cartService)
    {
        $subtotal   = $cartService->getTotalPrice();
        $taxSettings = $cartService->getTaxSettings();
        $taxAmount  = $cartService->calculateTaxAmount($subtotal);

        return inertia('Cart/Index', [
            'cartItems'          => $cartService->getCartItemsGrouped(),
            'total_quantity'     => $cartService->getTotalQuantity(),
            'total_price'        => $subtotal,
            'tax_rate'           => $taxSettings['tax_rate'],
            'tax_amount'         => $taxAmount,
            'prices_include_tax' => $taxSettings['prices_include_tax'],
            'grand_total'        => $cartService->getGrandTotal(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product, CartService $cartService)
    {
        $request->mergeIfMissing(['quantity' => 1]);
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'option_ids' => ['nullable', 'array'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        $cartService->addItemToCart(
            $product,
            $data['quantity'],
            $data['option_ids'] ?? null
        );

        return back()->with('success', 'Product added to cart successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, CartService $cartService)
    {
        $request->validate([
            'quantity' => ['integer', 'min:1'],
        ]);

        $optionIds = $request->input('option_ids', null);
        if ($optionIds) {
            $optionIds = array_map('intval', $optionIds);
        }
        $quantity = $request->input('quantity', 1);
        $cartService->updateItemQuantity($product->id, $quantity, $optionIds);

        return back()->with('success', 'Quantity updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product, CartService $cartService)
    {
        $cartItemId = $request->input('cart_item_id');
        $optionIds = $request->input('option_ids', null);
        if ($optionIds) {
            $optionIds = array_map('intval', $optionIds);
        }
        $cartService->removeItemFromCart($product->id, $optionIds, $cartItemId);

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Show the checkout page (shipping address + payment method selection).
     */
    public function checkoutPage(Request $request, CartService $cartService)
    {
        $vendorId = $request->query('vendor_id');
        $allCartItems = $cartService->getCartItemsGrouped();

        $checkoutItems = $allCartItems;
        if ($vendorId) {
            $checkoutItems = isset($allCartItems[$vendorId]) ? [$vendorId => $allCartItems[$vendorId]] : [];
        }

        $totalPrice = collect($checkoutItems)->sum('total_price');

        $taxSettings = $cartService->getTaxSettings();
        $taxAmount   = $cartService->calculateTaxAmount((float) $totalPrice);
        $grandTotal  = $taxSettings['prices_include_tax']
            ? $totalPrice
            : round($totalPrice + $taxAmount, 4);

        return inertia('Checkout/Index', [
            'checkoutItems'      => array_values($checkoutItems),
            'total_price'        => $totalPrice,
            'tax_rate'           => $taxSettings['tax_rate'],
            'tax_amount'         => $taxAmount,
            'prices_include_tax' => $taxSettings['prices_include_tax'],
            'grand_total'        => $grandTotal,
            'vendor_id'          => $vendorId,
        ]);
    }

    /**
     * Place orders — creates orders then handles payment method routing.
     * payment_method: 'cod' (cash on delivery) or 'paymob_cc' (Paymob credit card)
     */
    public function placeOrder(Request $request, CartService $cartService)
    {
        $data = $request->validate([
            'shipping_name'    => ['required', 'string', 'max:255'],
            'shipping_phone'   => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_city'    => ['required', 'string', 'max:100'],
            'shipping_state'   => ['nullable', 'string', 'max:100'],
            'shipping_country' => ['required', 'string', 'max:100'],
            'shipping_zip'     => ['nullable', 'string', 'max:20'],
            'payment_method'   => ['required', 'in:cod,paymob_cc'],
            'vendor_id'        => ['nullable', 'integer'],
            'coupon_code'      => ['nullable', 'string'],
        ]);

        $coupon = null;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper(trim($data['coupon_code'])))->first();
            if (!$coupon) {
                return back()->withErrors(['coupon_code' => 'Invalid coupon code.']);
            }
        }

        $vendorId = $data['vendor_id'] ?? null;
        $allCartItems = $cartService->getCartItemsGrouped();

        $checkoutCartItems = $allCartItems;
        if ($vendorId) {
            $checkoutCartItems = isset($allCartItems[$vendorId]) ? [$vendorId => $allCartItems[$vendorId]] : [];
        }

        if (empty($checkoutCartItems)) {
            return back()->with('error', 'Your cart is empty.');
        }

        DB::beginTransaction();
        try {
            $orders = [];
            $commissionRate  = (float) Setting::get('website_commission', 0);
            $taxRate         = (float) Setting::get('tax_rate', 0);
            $pricesIncludeTax = Setting::get('prices_include_tax', '0') === '1';

            // Compute coupon discount across all checkout items
            $checkoutTotal    = collect($checkoutCartItems)->sum('total_price');
            $totalDiscount    = 0;
            if ($coupon) {
                $couponError = $coupon->validate((float) $checkoutTotal);
                if ($couponError) {
                    return back()->withErrors(['coupon_code' => $couponError]);
                }
                $totalDiscount = $coupon->calculateDiscount((float) $checkoutTotal);
            }

            foreach ($checkoutCartItems as $item) {
                $user = $item['user'];
                $cartItems = $item['items'];

                $totalPrice = $item['total_price'];

                // Distribute discount proportionally across vendors
                $vendorDiscount = $checkoutTotal > 0
                    ? round($totalDiscount * ($totalPrice / $checkoutTotal), 4)
                    : 0;
                $discountedTotal   = max(0, $totalPrice - $vendorDiscount);

                // Calculate tax
                if ($taxRate > 0) {
                    if ($pricesIncludeTax) {
                        // Extract tax from inclusive price
                        $taxAmount = round($discountedTotal - $discountedTotal / (1 + $taxRate / 100), 4);
                    } else {
                        // Add tax on top
                        $taxAmount = round($discountedTotal * $taxRate / 100, 4);
                        $discountedTotal = round($discountedTotal + $taxAmount, 4);
                    }
                } else {
                    $taxAmount = 0;
                }

                $websiteCommission = round($discountedTotal * $commissionRate / 100, 4);
                $vendorSubtotal    = round($discountedTotal - $websiteCommission, 4);

                $order = Order::create([
                    'user_id'            => auth()->id(),
                    'vendor_user_id'     => $user['id'],
                    'total_price'        => $discountedTotal,
                    'discount_amount'    => $vendorDiscount,
                    'tax_rate'           => $taxRate,
                    'tax_amount'         => $taxAmount,
                    'coupon_code'        => $coupon ? $coupon->code : null,
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

                $orders[] = $order;

                foreach ($cartItems as $cartItem) {
                    $order->items()->create([
                        'product_id'                => $cartItem['product_id'],
                        'quantity'                  => $cartItem['quantity'],
                        'price'                     => $cartItem['price'],
                        'variation_type_option_ids' => $cartItem['option_ids'] ?? null,
                    ]);
                }
            }

            // Increment coupon usage once all orders are created
            if ($coupon) {
                $coupon->incrementUsage();
            }

            DB::commit();

            // Cash on delivery — clear cart and go to success
            if ($data['payment_method'] === 'cod') {
                $cartService->clearCart();
                return inertia('Checkout/Success', [
                    'message' => 'Order placed successfully! We will contact you to confirm delivery.',
                ]);
            }

            // Paymob CC — store order IDs in session and redirect to Paymob
            $orderIds = collect($orders)->pluck('id')->toArray();
            session(['paymob_order_ids' => $orderIds]);

            return redirect()->route('paymob.pay');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage() ?: 'Something went wrong. Please try again.');
        }
    }
}

