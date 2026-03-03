<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\OrderStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobController extends Controller
{
    private string $apiKey;
    private string $ccIntegrationId;
    private string $iframeId;
    private string $hmacSecret;
    private string $baseUrl = 'https://accept.paymob.com/api';

    public function __construct()
    {
        $this->apiKey          = config('paymob.api_key');
        $this->ccIntegrationId = config('paymob.cc_integration_id');
        $this->iframeId        = config('paymob.iframe_id');
        $this->hmacSecret      = config('paymob.hmac_secret');
    }

    /**
     * Authenticate with Paymob and retrieve an auth token.
     */
    private function getAuthToken(): string
    {
        $response = Http::post("{$this->baseUrl}/auth/tokens", [
            'api_key' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Paymob authentication failed: ' . $response->body());
        }

        return $response->json('token');
    }

    /**
     * Register an order in Paymob and return the Paymob order ID.
     */
    private function registerPaymobOrder(string $authToken, int $amountCents, array $items, string $currency = 'EGP'): int
    {
        $response = Http::post("{$this->baseUrl}/ecommerce/orders", [
            'auth_token'       => $authToken,
            'delivery_needed'  => false,
            'amount_cents'     => $amountCents,
            'currency'         => $currency,
            'items'            => $items,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Paymob order registration failed: ' . $response->body());
        }

        return $response->json('id');
    }

    /**
     * Request a Paymob payment key for the iframe.
     */
    private function getPaymentKey(
        string $authToken,
        int $paymobOrderId,
        int $amountCents,
        array $billingData,
        string $currency = 'EGP'
    ): string {
        $response = Http::post("{$this->baseUrl}/acceptance/payment_keys", [
            'auth_token'        => $authToken,
            'amount_cents'      => $amountCents,
            'expiration'        => 3600,
            'order_id'          => $paymobOrderId,
            'billing_data'      => $billingData,
            'currency'          => $currency,
            'integration_id'    => $this->ccIntegrationId,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Paymob payment key request failed: ' . $response->body());
        }

        return $response->json('token');
    }

    /**
     * Initiate a Paymob CC payment and redirect to the hosted iframe.
     */
    public function pay(Request $request)
    {
        $orderIds = session('paymob_order_ids', []);

        if (empty($orderIds)) {
            return back()->with('error', 'No orders found to pay.');
        }

        $orders = Order::with('items.product')
            ->whereIn('id', $orderIds)
            ->where('user_id', auth()->id())
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'Orders not found.');
        }

        $totalAmountCents = (int) ($orders->sum('total_price') * 100);

        $billingData = [
            'first_name'         => $orders->first()->shipping_name ?? auth()->user()->name,
            'last_name'          => ' ',
            'email'              => auth()->user()->email,
            'phone_number'       => $orders->first()->shipping_phone ?? '+20000000000',
            'apartment'          => 'NA',
            'floor'              => 'NA',
            'street'             => $orders->first()->shipping_address ?? 'NA',
            'building'           => 'NA',
            'shipping_method'    => 'PKG',
            'postal_code'        => $orders->first()->shipping_zip ?? '00000',
            'city'               => $orders->first()->shipping_city ?? 'NA',
            'country'            => $orders->first()->shipping_country ?? 'EG',
            'state'              => $orders->first()->shipping_state ?? 'NA',
        ];

        $lineItems = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $lineItems[] = [
                    'name'        => $item->product->title ?? 'Product',
                    'amount_cents'=> (int) ($item->price * 100),
                    'description' => 'Product',
                    'quantity'    => $item->quantity,
                ];
            }
        }

        try {
            $authToken     = $this->getAuthToken();
            $paymobOrderId = $this->registerPaymobOrder($authToken, $totalAmountCents, $lineItems);
            $paymentKey    = $this->getPaymentKey($authToken, $paymobOrderId, $totalAmountCents, $billingData);

            // Store the Paymob order ID on each local order
            Order::whereIn('id', $orderIds)->update([
                'paymob_order_id' => $paymobOrderId,
                'payment_method'  => 'paymob_cc',
            ]);

            $iframeUrl = "https://accept.paymob.com/api/acceptance/iframes/{$this->iframeId}?payment_token={$paymentKey}";

            // Clear the session order IDs
            session()->forget('paymob_order_ids');

            return redirect($iframeUrl);
        } catch (\Exception $e) {
            Log::error('Paymob pay error: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Handle Paymob transaction processed callback (webhook / redirect).
     * Paymob sends a POST with HMAC in `hmac` query param.
     */
    public function callback(Request $request)
    {
        $data = $request->all();

        // Verify HMAC
        if (! $this->verifyHmac($data)) {
            Log::warning('Paymob HMAC verification failed', $data);
            return response('Forbidden', 403);
        }

        $paymobOrderId = $data['obj']['order']['id'] ?? null;
        $success       = $data['obj']['success'] ?? false;
        $amountCents   = $data['obj']['amount_cents'] ?? 0;

        if ($paymobOrderId) {
            $orders = Order::where('paymob_order_id', $paymobOrderId)->get();
            foreach ($orders as $order) {
                $order->update([
                    'status'         => $success ? OrderStatusEnum::Paid->value : OrderStatusEnum::Failed->value,
                    'payment_intent' => $data['obj']['id'] ?? null,
                ]);
            }
        }

        return response('OK', 200);
    }

    /**
     * Redirect URL after Paymob hosted payment (GET).
     */
    public function response(Request $request)
    {
        $success = $request->query('success') === 'true';

        if ($success) {
            return inertia('Checkout/Success', [
                'message' => 'Payment successful! Your order is being processed.',
            ]);
        }

        return inertia('Checkout/Failure', [
            'message' => 'Payment failed or was cancelled. Please try again.',
        ]);
    }

    /**
     * Verify Paymob HMAC signature.
     */
    private function verifyHmac(array $data): bool
    {
        $hmac           = $data['hmac'] ?? null;
        $transactionObj = $data['obj'] ?? [];

        $fields = [
            'amount_cents', 'created_at', 'currency', 'error_occured',
            'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
            'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
            'is_voided', 'order_id', 'owner', 'pending', 'source_data_pan',
            'source_data_sub_type', 'source_data_type', 'success',
        ];

        $concatenated = '';
        foreach ($fields as $field) {
            if ($field === 'order_id') {
                $concatenated .= $transactionObj['order']['id'] ?? '';
            } elseif (str_starts_with($field, 'source_data_')) {
                $subField      = str_replace('source_data_', '', $field);
                $concatenated .= $transactionObj['source_data'][$subField] ?? '';
            } else {
                $val = $transactionObj[$field] ?? '';
                if (is_bool($val)) {
                    $val = $val ? 'true' : 'false';
                }
                $concatenated .= $val;
            }
        }

        $calculatedHmac = hash_hmac('sha512', $concatenated, $this->hmacSecret);

        return hash_equals($calculatedHmac, (string) $hmac);
    }
}
