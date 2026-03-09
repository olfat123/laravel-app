<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 640px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .header {
            background-color: #1e3a5f;
            padding: 24px;
            text-align: center;
            color: #ffffff;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .body {
            padding: 32px 24px;
            color: #374151;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: .05em;
            margin: 24px 0 8px;
        }

        .info-grid {
            background: #f9fafb;
            border-radius: 6px;
            padding: 16px;
        }

        .info-grid p {
            margin: 6px 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #e5e7eb;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .total-row td {
            font-weight: bold;
            background: #f9fafb;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }} — New Order Received</h1>
            <p style="margin:6px 0 0;font-size:14px;">Order #{{ $order->id }}</p>
        </div>
        <div class="body">

            <p class="section-title">Order Summary</p>
            <div class="info-grid">
                <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                @if ($order->coupon_code)
                    <p><strong>Coupon:</strong> {{ $order->coupon_code }} (-
                        ${{ number_format($order->discount_amount, 2) }})</p>
                @endif
            </div>

            <p class="section-title">Customer</p>
            <div class="info-grid">
                <p><strong>Name:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
            </div>

            <p class="section-title">Shipping Address</p>
            <div class="info-grid">
                <p>{{ $order->shipping_name }}</p>
                <p>{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }}</p>
                <p>{{ $order->shipping_country }} {{ $order->shipping_zip }}</p>
                <p>Phone: {{ $order->shipping_phone }}</p>
            </div>

            <p class="section-title">Items</p>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'Product #' . $item->product_id }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                    @if ($order->discount_amount > 0)
                        <tr>
                            <td colspan="3" style="text-align:right;">Discount</td>
                            <td>- ${{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($order->tax_amount > 0)
                        <tr>
                            <td colspan="3" style="text-align:right;">Tax ({{ $order->tax_rate }}%)</td>
                            <td>${{ number_format($order->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="3" style="text-align:right;">Total</td>
                        <td>${{ number_format($order->total_price, 2) }}</td>
                    </tr>
                </tbody>
            </table>

        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Internal notification — do not reply.
        </div>
    </div>
</body>

</html>
