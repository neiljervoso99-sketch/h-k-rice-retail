<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Orders | H&K Consumer Goods Store</title>
    @vite('resources/js/app.js')

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7f2;
            margin: 0;
            padding: 20px;
            color: #253528;
            font-size: 13px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        h1 {
            color: #245b35;
            font-size: 24px;
            margin: 18px 0 8px;
        }

        h3 {
            font-size: 16px;
            margin: 0 0 8px;
        }

        p {
            font-size: 13px;
            line-height: 1.5;
            margin: 8px 0;
        }

        .home-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 14px;
            background: #24683a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 7px;
            transition: background 0.2s ease;
        }

        .home-button:hover {
            background: #194d2a;
            color: #ffffff;
        }

        .order {
            background: #ffffff;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px #0000000d;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            background: #e5eee5;
            color: #245b35;
            font-size: 11px;
            text-transform: capitalize;
        }

        .button {
            display: inline-block;
            padding: 8px 12px;
            background: #24683a;
            color: #ffffff;
            font-size: 12px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 6px;
        }

        .button:hover {
            background: #194d2a;
        }

        .muted {
            color: #66736a;
        }

        .empty {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
        }

        @media (max-width: 600px) {
            body {
                padding: 14px;
            }

            h1 {
                font-size: 21px;
            }

            .order {
                padding: 14px;
            }

            .home-button,
            .button {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
            }

            .top {
                align-items: stretch;
            }

            .container {
                overflow-wrap: anywhere;
            }
        }
    </style>
</head>

<body>
<div class="notification-floating">
    @include('components.notification-center')
</div>
<div class="container">

    <a href="{{ url('/') }}" class="home-button">
        ← Back to Home
    </a>

    <h1>My Orders</h1>
    <p class="muted">View your order history and current order status.</p>

    @forelse ($orders as $order)
        <div class="order">
            <div class="top">
                <div>
                    <h3>Order #{{ $order->id }}</h3>
                    <p class="muted">
                        Placed: {{ $order->created_at?->format('M d, Y h:i A') ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <span class="status">
                        {{ $order->display_status }}
                    </span>
                </div>
            </div>

            <p>
                <strong>Items:</strong>
                {{ $order->items->sum('quantity') }}
            </p>

            <p>
                <strong>Total:</strong>
                ₱{{ number_format((float) ($order->total ?? $order->total_amount ?? 0), 2) }}
            </p>

            @if ($order->processing_at || $order->shipped_at || $order->delivered_at || $order->cancelled_at)
                <p class="muted">
                    @if ($order->processing_at) Processing: {{ $order->processing_at->format('M d, Y h:i A') }} @endif
                    @if ($order->shipped_at) · Shipped: {{ $order->shipped_at->format('M d, Y h:i A') }} @endif
                    @if ($order->delivered_at) · Delivered: {{ $order->delivered_at->format('M d, Y h:i A') }} @endif
                    @if ($order->cancelled_at) · Cancelled: {{ $order->cancelled_at->format('M d, Y h:i A') }} @endif
                </p>
            @endif

            <a class="button"
               href="{{ route('customer.orders.show', $order->id) }}">
                View Order Details
            </a>
        </div>
    @empty
        <div class="empty">
            <h3>No orders yet</h3>
            <p>Your orders will appear here after you place an order.</p>
            <a class="button" href="{{ url('/') }}">Browse Products</a>
        </div>
    @endforelse

    {{ $orders->links() }}

</div>
</body>
</html>