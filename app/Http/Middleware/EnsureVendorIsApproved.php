<?php

namespace App\Http\Middleware;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('filament.admin.auth.login');
        }

        // Admins always pass
        if ($user->hasRole(RolesEnum::Admin->value)) {
            return $next($request);
        }

        // Vendors must be approved to access the admin panel
        if ($user->hasRole(RolesEnum::Vendor->value)) {
            $vendor = $user->vendor;

            if (! $vendor || $vendor->status !== VendorStatusEnum::APPROVED->value) {
                // Keep them logged in — just block admin access and send to frontend
                return redirect()->route('home')
                    ->with('error', 'Your vendor account is pending approval. You will be notified once it is approved.');
            }

            return $next($request);
        }

        // Any other role → deny
        abort(403, 'Unauthorized.');
    }
}
