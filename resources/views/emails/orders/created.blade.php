@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html dir="{{ $isRtl ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('email.order_created.title') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .header {
            background-color: #4f46e5;
            padding: 24px;
            text-align: center;
            color: #ffffff;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
        }

        .body {
            padding: 32px 24px;
            color: #374151;
        }

        .body h2 {
            margin-top: 0;
            font-size: 18px;
        }

        .order-info {
            background: #f9fafb;
            border-radius: 6px;
            padding: 16px;
            margin: 20px 0;
        }

        .order-info p {
            margin: 6px 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            font-size: 14px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #e5e7eb;
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
            <h1>{{ config('app.name') }}</h1>
            <p style="margin:6px 0 0;font-size:14px;">{{ __('email.order_created.header_subtitle') }}</p>
        </div>
        <div class="body">
            <h2>{{ __('email.order_created.greeting', ['name' => $order->user->name]) }}</h2>
            <p>{{ __('email.order_created.intro') }}</p>

            <div class="order-info">
                <p><strong>{{ __('email.order_created.order_id') }}:</strong> #{{ $order->id }}</p>
                <p><strong>{{ __('email.order_created.order_date') }}:</strong>
                    {{ $order->created_at->format('M d, Y H:i') }}</p>
                <p><strong>{{ __('email.order_created.payment_method') }}:</strong>
                    {{ ucfirst($order->payment_method) }}</p>
                <p><strong>{{ __('email.order_created.status') }}:</strong> {{ ucfirst($order->status) }}</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>{{ __('email.order_created.col_product') }}</th>
                        <th>{{ __('email.order_created.col_qty') }}</th>
                        <th>{{ __('email.order_created.col_price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'Product #' . $item->product_id }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                        </tr>
                    @endforeach
                    @if ($order->discount_amount > 0)
                        <tr>
                            <td colspan="2" style="text-align:right;">{{ __('email.order_created.discount') }}</td>
                            <td>- ${{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($order->tax_amount > 0)
                        <tr>
                            <td colspan="2" style="text-align:right;">{{ __('email.order_created.tax') }}</td>
                            <td>${{ number_format($order->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="2" style="text-align:right;">{{ __('email.order_created.total') }}</td>
                        <td>${{ number_format($order->total_price, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="order-info" style="margin-top:24px;">
                <p><strong>{{ __('email.order_created.shipping_to') }}:</strong></p>
                <p>{{ $order->shipping_name }}</p>
                <p>{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }}</p>
                <p>{{ $order->shipping_country }} {{ $order->shipping_zip }}</p>
            </div>

            <p style="margin-top:24px;">{{ __('email.order_created.support_note') }}</p>
            <p>{{ __('email.order_created.thank_you') }}</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('email.footer_rights') }}
        </div>
    </div>
</body>

</html>
