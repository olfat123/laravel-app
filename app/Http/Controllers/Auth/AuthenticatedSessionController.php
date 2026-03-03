<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Services\CartService;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, CartService $cartService): \Symfony\Component\HttpFoundation\Response
    {
        $request->authenticate();

        $request->session()->regenerate();
        $user = Auth::user();
        $cartService->moveCartItemsToDatabase($user->id);

        if ($user->hasAnyRole([RolesEnum::Admin, RolesEnum::Vendor])) {
            // Only send approved vendors (and admins) to the admin panel
            if ($user->hasRole(RolesEnum::Vendor)) {
                $vendor = $user->vendor;
                if (! $vendor || $vendor->status !== VendorStatusEnum::APPROVED->value) {
                    return redirect()->route('home')
                        ->with('error', 'Your vendor account is pending approval. You will be notified once approved.');
                }
            }
            return Inertia::location(route('filament.admin.pages.dashboard', absolute: false));
        }

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
