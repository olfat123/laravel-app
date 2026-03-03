<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\CartService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request, CartService $cartService)
    {
        $request->validate([
            'code'      => 'required|string',
            'vendor_id' => 'nullable|integer',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $vendorId = $request->input('vendor_id');

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code.'], 422);
        }

        // Calculate total for this checkout
        $allCartItems = $cartService->getCartItemsGrouped();
        $checkoutItems = $allCartItems;
        if ($vendorId) {
            $checkoutItems = isset($allCartItems[$vendorId]) ? [$vendorId => $allCartItems[$vendorId]] : [];
        }
        $orderTotal = collect($checkoutItems)->sum('total_price');

        $error = $coupon->validate((float) $orderTotal);
        if ($error) {
            return response()->json(['error' => $error], 422);
        }

        $discount = $coupon->calculateDiscount((float) $orderTotal);

        return response()->json([
            'code'            => $coupon->code,
            'type'            => $coupon->type,
            'value'           => $coupon->value,
            'discount_amount' => $discount,
            'final_total'     => max(0, $orderTotal - $discount),
        ]);
    }

    public function remove()
    {
        return response()->json(['removed' => true]);
    }
}
