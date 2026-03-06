<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\UserAddress;
use App\Enums\OrderStatusEnum;
use App\Services\CartService;
use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductListResource;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Orders with items and product image
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product' => fn ($q) => $q
                ->withCount('variationTypes')
                ->with(['media', 'user', 'department', 'category']),
            ])
            ->latest()
            ->get()
            ->map(fn ($order) => [
                'id'               => $order->id,
                'status'           => $order->status,
                'total_price'      => $order->total_price,
                'payment_method'   => $order->payment_method,
                'created_at'       => $order->created_at->format('M d, Y'),
                'shipping_name'    => $order->shipping_name,
                'shipping_address' => $order->shipping_address,
                'shipping_city'    => $order->shipping_city,
                'shipping_country' => $order->shipping_country,
                'items' => $order->items->map(fn ($item) => [
                    'id'       => $item->id,
                    'quantity' => $item->quantity,
                    'price'    => $item->price,
                    'product'  => $item->product ? [
                        'id'        => $item->product->id,
                        'title'     => $item->product->title,
                        'slug'      => $item->product->slug,
                        'image_url' => $item->product->getFirstMediaUrl('images', 'small') ?: null,
                    ] : null,
                ]),
            ]);

        // Wishlist products
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with(['product' => fn ($q) => $q
                ->withCount('variationTypes')
                ->with(['media', 'user', 'department', 'category']),
            ])
            ->get()
            ->filter(fn ($w) => $w->product !== null)
            ->map(fn ($w) => new ProductListResource($w->product));

        // Addresses
        $addresses = UserAddress::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->get();

        return Inertia::render('Account/Index', [
            'orders'    => $orders,
            'wishlist'  => $wishlistItems->values(),
            'addresses' => $addresses,
        ]);
    }

    public function toggleWishlist(Product $product)
    {
        $user = Auth::user();

        $entry = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($entry) {
            $entry->delete();
            $inWishlist = false;
        } else {
            Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);
            $inWishlist = true;
        }

        return back()->with('success', $inWishlist ? 'Added to favourites.' : 'Removed from favourites.');
    }

    public function storeAddress(AddressRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();
        $data['user_id'] = $user->id;

        if (! empty($data['is_default'])) {
            UserAddress::where('user_id', $user->id)->update(['is_default' => false]);
        }

        // If first address, make it default automatically
        if (UserAddress::where('user_id', $user->id)->doesntExist()) {
            $data['is_default'] = true;
        }

        UserAddress::create($data);

        return back()->with('success', 'Address saved.');
    }

    public function updateAddress(AddressRequest $request, UserAddress $address)
    {
        abort_if($address->user_id !== Auth::id(), 403);

        $data = $request->validated();

        if (! empty($data['is_default'])) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('success', 'Address updated.');
    }

    public function deleteAddress(UserAddress $address)
    {
        abort_if($address->user_id !== Auth::id(), 403);
        $address->delete();

        return back()->with('success', 'Address deleted.');
    }

    public function setDefaultAddress(UserAddress $address)
    {
        abort_if($address->user_id !== Auth::id(), 403);

        UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Default address updated.');
    }

    public function reorder(Order $order, CartService $cartService)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        $items = $order->items()->with('product')->get();
        $added = 0;

        foreach ($items as $item) {
            if (!$item->product) continue;

            $optionIds = $item->variation_type_option_ids ?? [];
            $cartService->addItemToCart($item->product, $item->quantity, count($optionIds) ? $optionIds : null);
            $added++;
        }

        if ($added === 0) {
            return redirect()->route('cart.index')->with('error', 'No available products from this order could be added to your cart.');
        }

        return redirect()->route('cart.index')->with('success', "Order #{$order->id} items added to your cart.");
    }

    public function cancelOrder(Order $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        $cancellable = [
            OrderStatusEnum::Pending->value,
            OrderStatusEnum::Processing->value,
        ];

        if (!in_array($order->status, $cancellable)) {
            return back()->with('error', 'This order cannot be cancelled at its current status.');
        }

        $order->update(['status' => OrderStatusEnum::Cancelled->value]);

        return back()->with('success', "Order #{$order->id} has been cancelled.");
    }
}
