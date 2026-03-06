<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class, fn () => new CartService());

        $this->app->singleton(OrderService::class, fn ($app) => new OrderService(
            $app->make(CartService::class)
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
