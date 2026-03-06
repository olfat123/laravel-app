<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Models\Wishlist;
use App\Models\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Load site settings (cached per-process, cleared on save)
        $siteSettings = Cache::rememberForever('site_settings', function () {
            $rows = Setting::whereIn('key', [
                'currency', 'enabled_languages', 'default_language',
            ])->pluck('value', 'key');

            return [
                'currency'          => $rows->get('currency', 'USD'),
                'available_locales' => json_decode($rows->get('enabled_languages', '["en","ar"]'), true) ?: ['en'],
                'default_language'  => $rows->get('default_language', 'en'),
            ];
        });

        // Currency number-formatting locale is always derived from the active UI language.
        $localeToCurrencyLocale = [
            'en' => 'en-US',
            'ar' => 'ar-EG',
            'fr' => 'fr-FR',
            'de' => 'de-DE',
            'tr' => 'tr-TR',
        ];

        $availableLocales = $siteSettings['available_locales'];
        $defaultLocale    = $siteSettings['default_language'];

        // Apply stored locale — fall back to default if no longer enabled
        $sessionLocale = session('locale');
        $locale = ($sessionLocale && in_array($sessionLocale, $availableLocales))
            ? $sessionLocale
            : $defaultLocale;

        App::setLocale($locale);

        // Load translation JSON
        $translationPath = base_path("lang/{$locale}.json");
        $translations = file_exists($translationPath)
            ? json_decode(file_get_contents($translationPath), true)
            : [];

        $cartService = app(CartService::class);
        $totalQuantity = $cartService->getTotalQuantity();
        $totalPrice = $cartService->getTotalPrice();
        $cartItems = $cartService->getCartItems();
        
        return [
            ...parent::share($request),
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'success' => fn () => session('success'),
            'error' => fn () => session('error'),
            'totalQuantity' => $totalQuantity,
            'totalPrice' => $totalPrice,
            'cartItems' => $cartItems,
            'wishlistedProductIds' => fn () => $request->user()
                ? Wishlist::where('user_id', $request->user()->id)->pluck('product_id')->toArray()
                : [],
            'locale'           => $locale,
            'translations'     => $translations,
            'currency'         => $siteSettings['currency'],
            'currencyLocale'   => $localeToCurrencyLocale[$locale] ?? 'en-US',
            'availableLocales' => $availableLocales,
        ];
    }
}
