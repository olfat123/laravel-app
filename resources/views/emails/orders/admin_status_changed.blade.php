<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Changed</title>
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

        .status-box {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px 24px;
            margin: 16px 0;
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
            <h1>{{ config('app.name') }} — Order Status Changed</h1>
            <p style="margin:6px 0 0;font-size:14px;">Order #{{ $order->id }}</p>
        </div>
        <div class="body">

            <p class="section-title">Status Change</p>
            <div class="status-box">
                <span class="status-badge status-old">{{ ucfirst($oldStatus) }}</span>
                <span class="arrow">&#8594;</span>
                <span class="status-badge status-new">{{ ucfirst($newStatus) }}</span>
            </div>

            <p class="section-title">Order Details</p>
            <div class="info-grid">
                <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Total:</strong> ${{ number_format($order->total_price, 2) }}</p>
                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
            </div>

            <p class="section-title">Customer</p>
            <div class="info-grid">
                <p><strong>Name:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
            </div>

        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Internal notification — do not reply.
        </div>
    </div>
</body>

</html>
