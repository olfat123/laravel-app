<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role'          => ['required', 'in:customer,vendor'],
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'store_name'    => ['required_if:role,vendor', 'nullable', 'string', 'max:255'],
            'store_address' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->role === 'vendor') {
            $user->assignRole(RolesEnum::Vendor->value);

            Vendor::create([
                'user_id'       => $user->id,
                'status'        => VendorStatusEnum::PENDING->value,
                'store_name'    => $request->store_name,
                'store_address' => $request->store_address,
            ]);
        } else {
            $user->assignRole(RolesEnum::Customer->value);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home', absolute: false));
    }
}
