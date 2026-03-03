<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function webhook(Request $request)
    {
        // Handle Stripe webhook events here
    }

    public function success(Request $request)
    {
        // Handle successful payment here
    }

    public function failure(Request $request)
    {
        // Handle canceled payment here
    }
}
