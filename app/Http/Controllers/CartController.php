<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Services\OrderService;

class CartController extends Controller
{
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
     * Place orders — delegates all business logic to OrderService.
     * payment_method: 'cod' (cash on delivery) or 'paymob_cc' (Paymob credit card)
     */
    public function placeOrder(Request $request, CartService $cartService, OrderService $orderService)
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

        try {
            $orders = $orderService->placeOrders($data, $data['vendor_id'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['coupon_code' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Something went wrong. Please try again.');
        }

        if ($data['payment_method'] === 'cod') {
            $cartService->clearCart();
            return inertia('Checkout/Success', [
                'message' => 'Order placed successfully! We will contact you to confirm delivery.',
            ]);
        }

        session(['paymob_order_ids' => collect($orders)->pluck('id')->toArray()]);

        return redirect()->route('paymob.pay');
    }
}

