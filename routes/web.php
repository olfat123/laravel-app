<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymobController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\LanguageController;

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/shop', [ProductController::class, 'shop'])->name('shop');
Route::get('/store/{vendor:store_slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])
    ->name('product.show');

Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/add/{product}', 'store')->name('store');
    Route::put('/{product}', 'update')->name('update');
    Route::delete('/{product}', 'destroy')->name('destroy');
});

// Auth-protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Coupon
    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');

    // Checkout page and order placement
    Route::get('/checkout', [CartController::class, 'checkoutPage'])->name('cart.checkout');
    Route::post('/checkout/place-order', [CartController::class, 'placeOrder'])->name('cart.place-order');

    // Paymob — redirect to Paymob hosted iframe
    Route::get('/paymob/pay', [PaymobController::class, 'pay'])->name('paymob.pay');
    // Paymob — redirect back after hosted payment
    Route::get('/paymob/response', [PaymobController::class, 'response'])->name('paymob.response');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Account (orders, wishlist, addresses)
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::post('/account/wishlist/{product}', [AccountController::class, 'toggleWishlist'])->name('account.wishlist.toggle');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('account.addresses.update');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'deleteAddress'])->name('account.addresses.delete');
    Route::post('/account/addresses/{address}/default', [AccountController::class, 'setDefaultAddress'])->name('account.addresses.default');
    Route::post('/account/orders/{order}/reorder', [AccountController::class, 'reorder'])->name('account.orders.reorder');
    Route::post('/account/orders/{order}/cancel', [AccountController::class, 'cancelOrder'])->name('account.orders.cancel');
});

// Paymob webhook — excluded from CSRF (configured in bootstrap/app.php)
Route::post('/paymob/callback', [PaymobController::class, 'callback'])->name('paymob.callback');

require __DIR__.'/auth.php';

