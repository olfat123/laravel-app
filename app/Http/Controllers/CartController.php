<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
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
        // Checkout logic will be implemented here
    }
}
