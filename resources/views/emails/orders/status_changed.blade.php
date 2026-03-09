@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html dir="{{ $isRtl ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('email.order_status.title') }}</title>
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

        .status-box {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px 24px;
            margin: 24px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-old {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-new {
            background: #d1fae5;
            color: #065f46;
        }

        .arrow {
            font-size: 20px;
            color: #6b7280;
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
            <p style="margin:6px 0 0;font-size:14px;">{{ __('email.order_status.header_subtitle') }}</p>
        </div>
        <div class="body">
            <h2>{{ __('email.order_status.greeting', ['name' => $order->user->name]) }}</h2>
            <p>{!! __('email.order_status.intro', ['id' => '<strong>#' . $order->id . '</strong>']) !!}</p>

            <div class="status-box">
                <span class="status-badge status-old">{{ ucfirst($oldStatus) }}</span>
                <span class="arrow">{{ $isRtl ? '&#8592;' : '&#8594;' }}</span>
                <span class="status-badge status-new">{{ ucfirst($newStatus) }}</span>
            </div>

            <div class="order-info">
                <p><strong>{{ __('email.order_status.order_id') }}:</strong> #{{ $order->id }}</p>
                <p><strong>{{ __('email.order_status.order_date') }}:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                <p><strong>{{ __('email.order_status.total') }}:</strong> ${{ number_format($order->total_price, 2) }}</p>
            </div>

            @if ($newStatus === 'shipped')
                <p>{{ __('email.order_status.msg_shipped') }}</p>
            @elseif($newStatus === 'delivered' || $newStatus === 'completed')
                <p>{{ __('email.order_status.msg_delivered') }}</p>
            @elseif($newStatus === 'cancelled')
                <p>{{ __('email.order_status.msg_cancelled') }}</p>
            @elseif($newStatus === 'refunded')
                <p>{{ __('email.order_status.msg_refunded') }}</p>
            @else
                <p>{{ __('email.order_status.msg_default') }}</p>
            @endif

            <p>{{ __('email.order_status.valued_customer') }}</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('email.footer_rights') }}
        </div>
    </div>
</body>

</html>