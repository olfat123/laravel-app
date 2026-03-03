<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Enums\OrderStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Attributes\Log;

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
     * Display a listing of the resource.
     */
    public function index(CartService $cartService)
    {
        return inertia('Cart/Index', [
            'cartItems' => $cartService->getCartItemsGrouped(),
            'total_quantity' => $cartService->getTotalQuantity(),
            'total_price' => $cartService->getTotalPrice(),
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
            'quantity' => [ 'integer', 'min:1'],
        ]);

        $optionIds = $request->input('option_ids', null);
        $quantity = $request->input('quantity', 1);
        $cartService->updateItemQuantity($product->id, $quantity, $optionIds);

        return back()->with('success', 'Quantity updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product, CartService $cartService)
    {
        $optionIds = $request->input('option_ids', null);
        $cartService->removeItemFromCart($product->id, $optionIds);
    }

    /**
     * Handle checkout process.
     */
    public function checkout(Request $request, CartService $cartService)
    {
        \Stripe\Stripe::setApiKey(config('app.stripe_secret_key'));

        $vendorId = $request->input('vendor_id');
        $allCartItems = $cartService->getCartItemsGrouped();
        DB::beginTransaction();
        try {
            $checkoutCartItems = $allCartItems;
            if($vendorId) {
                $checkoutCartItems = [$allCartItems[$vendorId] ?? []];
            }
            $orders = [];
            $lineItems = [];
            foreach ($checkoutCartItems as $item) {
                $user = $item['user'];
                $cartItems = $item['items'];
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'vendor_user_id' => $user['id'],
                    'total_price' => $item['total_price'],
                    'stripe_session_id' => null,
                    'status' => OrderStatusEnum::Pending->value,
                ]);
                $orders[] = $order;
                foreach ($cartItems as $cartItem) {
                    $order->items()->create([
                        'product_id' => $cartItem['product_id'],
                        'quantity' => $cartItem['quantity'],
                        'price' => $cartItem['price'],
                        'variation_type_option_ids' => $cartItem['option_ids'] ?? null,
                    ]);

                    $description = collect($cartItem['options'] ?? [])->map(function ($item) {
                        return "{$item['type']['name']}: {$item['name']}";
                    })->implode(', ');

                    $imageUrl = $this->sanitizeImageUrl($cartItem['image'] ?? null);
                    $productData = [
                        'name' => $cartItem['title'],
                    ];
                    if ($imageUrl) {
                        $productData['images'] = [$imageUrl];
                    }
                    $lineItem = [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => $productData,
                            'unit_amount' => (int) ($cartItem['price'] * 100),
                        ],
                        'quantity' => $cartItem['quantity'],
                    ];
                    if (!empty($description)) {
                        $lineItem['price_data']['product_data']['description'] = $description;
                    }
                    $lineItems[] = $lineItem;
                    if (!empty($cartItem['option_ids'])) {
                        $lineItem['price_data']['product_data']['metadata'] = [
                            'variation_option_ids' => implode(',', $cartItem['option_ids']),
                        ];
                    }
                }
            }
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $request->user()->email,
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('stripe.success', []) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.failure', []),
            ]);
            foreach ($orders as $order) {
                $order->update(['stripe_session_id' => $session->id]);
            }
            DB::commit();
            return redirect($session->url);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage() ?: 'Something went wrong while processing your checkout. Please try again.');
        }
    }
}
