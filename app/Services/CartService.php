<?php

namespace App\Services;

use App\Models\Product;
use App\Models\CartItem;
use App\Models\VariationType;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use PHPUnit\Event\Runtime\PHP;

class CartService
{
    private ?array $cachedCartItems = null;
    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60 * 24 * 365;

    public function addItemToCart(Product $product, int $quantity = 1, $optionIds = null)
    {
        if ( $optionIds === null ) {
            $optionIds = $product->variationTypes
                ->mapWithKeys( fn( VariationType $type ) => [ $type->id => $type->options[0]?->id ] )
                ->toArray();
        }

        $price = $product->getPriceForOptions($optionIds);
        if ( Auth::check() ) {
            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        } else {
            $this->saveItemToCookies($product->id, $quantity, $price, $optionIds);
        }
    }

    public function updateItemQuantity(int $productId, int $quantity, $optionIds = null)
    {
        if ( Auth::check() ) {
            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        } else {
            $this->updateItemQuantityInCookies($productId, $quantity, $optionIds);
        }
    }

    public function removeItemFromCart(int $productId, $optionIds = null)
    {
        if ( Auth::check() ) {
            $this->removeItemFromDatabase($productId, $optionIds);
        } else {
            $this->removeItemFromCookies($productId, $optionIds);
        }
    }

    public function getCartItems(): array
    {
        try {
            if ( $this->cachedCartItems === null ) {
                $cartItems = [];
                if ( Auth::check() ) {
                    $cartItems = $this->getCartItemsFromDatabase();
                } else {
                    $cartItems = $this->getCartItemsFromCookies();
                }
                $productIds = collect($cartItems)->map(fn($item) => $item['product_id']);
                $products = Product::whereIn('id', $productIds)
                    ->with('user.vendor')
                    ->forWebsite()
                    ->get()
                    ->keyBy('id');

                $cartItemData = [];
                foreach ( $cartItems as $key => $cartItem ) {
                    if ( isset($products[$cartItem['product_id']]) ) {
                        $product = data_get($products, $cartItem['product_id']);
                        if (!$product) continue;

                        $optionInfo = [];
                        $optionIds = $cartItem['option_ids'] ?? [];
                        if (!is_array($optionIds)) {
                            $optionIds = [];
                        }
                        $options = VariationTypeOption::with('variationType')
                            ->whereIn('id', $optionIds)
                            ->get()
                            ->keyBy('id');

                        $imageUrl = null;

                        foreach ( $optionIds as $option_id ) {
                            $option = data_get($options, $option_id);
                            if(!$imageUrl){
                                $imageUrl = $option?->getFirstMediaUrl('images', 'small') ?: null;
                            }
                            $optionInfo[] = [
                                'id' => $option?->id,
                                'name' => $option?->name,
                                'type' => [
                                    'id' => $option?->variationType?->id,
                                    'name' => $option?->variationType?->name,
                                ]
                            ];
                        }
                        $cartItemData[] = [
                            'id'         => $cartItem['id'],
                            'product_id' => $product->id,
                            'title'      => $product->title,
                            'slug'       => $product->slug,
                            'price'      => $cartItem['price'],
                            'quantity'   => $cartItem['quantity'],
                            'option_ids' => $optionIds,
                            'options'    => $optionInfo,
                            'image'  => $imageUrl ?: $product->getFirstMediaUrl('images', 'small'),
                            'user' => [
                                'id' => $product->created_by,
                                'name' => $product->user?->vendor?->store_name,
                            ],
                        ];

                    }
                }

                $this->cachedCartItems = $cartItemData;
            }

            return $this->cachedCartItems;
        } catch (\Exception $e) {
            throw $e;
            Log::error( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
        }
        return [];
    }

    public function getTotalQuantity(): int
    {
        $totalQuantity = 0;
        foreach ( $this->getCartItems() as $item ) {
            $totalQuantity += $item['quantity'];
        }

        return $totalQuantity;
    }

    public function getTotalPrice(): float
    {
        $totalPrice = 0;
        foreach ( $this->getCartItems() as $item ) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        return $totalPrice;
    }

    protected function updateItemQuantityInDatabase(int $productId, int $quantity, $optionIds = null): void
    {
        $userId = Auth::id();

        $query = CartItem::where('user_id', $userId)
            ->where('product_id', $productId);

        if ($optionIds) {
            foreach ($optionIds as $typeId => $optionId) {
                $query->where("variation_type_option_ids->$typeId", $optionId);
            }
        } else {
            $query->whereNull('variation_type_option_ids');
        }

        $cartItem = $query->first();

        if ($cartItem) {
            $cartItem->update(['quantity' => $quantity]);
        }
    }

    protected function updateItemQuantityInCookies(int $productId, int $quantity, $optionIds = null): void
    {
        $cartItems = $this->getCartItemsFromCookies();
        if ($optionIds) {
            ksort($optionIds);
        }
        $itemKey = $productId . '_' . ( $optionIds ? json_encode( $optionIds ) : 'no_options' );
        if ( isset( $cartItems[ $itemKey ] ) ) {
            $cartItems[ $itemKey ]['quantity'] = $quantity;
            // Update the cookie with the new cart items
        }

        // Update the session with the new cart items
        Cookie::queue( self::COOKIE_NAME, json_encode( $cartItems ), self::COOKIE_LIFETIME );
    }

    protected function saveItemToDatabase(int $productId, int $quantity, $price, $optionIds = null): void
    {
        $userId = Auth::id();
        ksort($optionIds);
        $cartItem = CartItem::where( 'user_id', $userId )
            ->where( 'product_id', $productId )
            ->where( 'variation_type_option_ids', $optionIds ? json_encode( $optionIds ) : null )
            ->first();

        if ( $cartItem ) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
            return;
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'variation_type_option_ids' => $optionIds ? json_encode($optionIds) : null,
                'quantity' => $quantity,
                'price' => $price,
            ]);
            return;
        }

    }

    protected function saveItemToCookies(int $productId, int $quantity, $price, $optionIds = null): void
    {
        $cartItems = $this->getCartItemsFromCookies();
        if ($optionIds) {
            ksort($optionIds);
        }
        $itemKey = $productId . '_' . ( $optionIds ? json_encode( $optionIds ) : 'no_options' );

        if ( isset( $cartItems[ $itemKey ] ) ) {
            $cartItems[ $itemKey ]['quantity'] += $quantity;
            $cartItems[ $itemKey ]['price'] = $price; // Update price in case it has changed
        } else {
            $cartItems[ $itemKey ] = [
                'id' => uniqid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $optionIds,
            ];
        }

        // Update the session with the new cart items
        Cookie::queue( self::COOKIE_NAME, json_encode( $cartItems ), self::COOKIE_LIFETIME );
    }

    protected function removeItemFromDatabase(int $productId, $optionIds = null): void
    {
        $userId = Auth::id();
        $query = CartItem::where('user_id', $userId)
            ->where('product_id', $productId);

        if ($optionIds) {
            foreach ($optionIds as $typeId => $optionId) {
                $query->where("variation_type_option_ids->$typeId", $optionId);
            }
        } else {
            $query->whereNull('variation_type_option_ids');
        }
        $query->delete();
    }

    protected function removeItemFromCookies(int $productId, $optionIds = null): void
    {
        $cartItems = $this->getCartItemsFromCookies();
        if ($optionIds) {
            ksort($optionIds);
        }
        $itemKey = $productId . '_' . ( $optionIds ? json_encode( $optionIds ) : 'no_options' );
        if ( isset( $cartItems[ $itemKey ] ) ) {
            unset( $cartItems[ $itemKey ] );
            // Update the cookie with the new cart items
            Cookie::queue( self::COOKIE_NAME, json_encode( $cartItems ), self::COOKIE_LIFETIME );
        }
    }

    protected function getCartItemsFromDatabase()
    {
        $userId = Auth::id();
        $cartItems = CartItem::where( 'user_id', $userId )
                ->get()
                ->map( function($cartItem) {
                    return [
                        'id'         => $cartItem->id,
                        'product_id' => $cartItem->product_id,
                        'quantity'   => $cartItem->quantity,
                        'price'      => $cartItem->price,
                        'option_ids' => is_string($cartItem->variation_type_option_ids) 
                            ? json_decode($cartItem->variation_type_option_ids, true) 
                            : $cartItem->variation_type_option_ids,
                    ];
                } )->toArray();

        return $cartItems;
    }

    protected function getCartItemsFromCookies()
    {
        $cartItems = json_decode( Cookie::get( self::COOKIE_NAME, '[]' ), true );
        return is_array( $cartItems ) ? $cartItems : [];
    }

    public function getCartItemsGrouped(): array
    {
        $cartItems = $this->getCartItems();
        return collect($cartItems)
            ->groupBy(fn($item) => $item['user']['id'])
            ->map(fn($items, $userId) => [
                'user' => $items[0]['user'],
                'items' => $items->toArray(),
                'total_quantity' => $items->sum('quantity'),
                'total_price' => $items->sum(fn($item) => $item['price'] * $item['quantity']),
            ])
            ->toArray();
    }

}
