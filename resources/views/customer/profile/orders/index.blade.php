
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Orders | H&K Consumer Goods Store</title>
    @vite('resources/js/app.js')

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #f5f7f5;
            color: #24352a;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .topbar, .order-header, .order-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .topbar { margin-bottom: 24px; }

        a { color: #21683a; text-decoration: none; }

        .button {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 8px;
            background: #21683a;
            color: white;
            border: 0;
            cursor: pointer;
        }

        .order-card {
            background: white;
            border: 1px solid #e1e8e1;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .muted { color: #68766b; font-size: 14px; }
        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #edf4ee;
            color: #21683a;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status.cancelled {
            background: #fcebea;
            color: #a52822;
        }

        .empty {
            text-align: center;
            padding: 48px 20px;
            background: white;
            border-radius: 14px;
        }

        @media (max-width: 600px) {
            body { padding: 14px; }
            .order-card { padding: 16px; }
            .topbar { align-items: flex-start; }
            .button { min-height: 44px; }
            .order-footer { align-items: stretch; }
        }
    </style>
</head>
<body>
<div class="notification-floating">
    @include('components.notification-center')
</div>
<div class="container">
    <div class="topbar">
        <div>
            <h1>My Orders</h1>
            <p class="muted">View your orders and delivery progress.</p>
        </div>

        <a href="{{ route('home') }}">← Back to Store</a>
    </div>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @forelse ($orders as $order)
        <section class="order-card">
            <div class="order-header">
                <div>
                    <h2>
                        {{ $order->order_number ?: 'Order #' . $order->id }}
                    </h2>

                    <p class="muted">
                        Placed {{ $order->created_at->format('M d, Y h:i A') }}
                    </p>
                </div>

                <span class="status {{ $order->status === 'cancelled' ? 'cancelled' : '' }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <p>
                <strong>Recipient:</strong>
                {{ $order->recipient_name }}
            </p>

            <p>
                <strong>Delivery address:</strong>
                {{ $order->address_line }},
                {{ $order->barangay }},
                {{ $order->municipality }},
                {{ $order->province }}
                {{ $order->postal_code }}
            </p>

            <p>
                <strong>Total:</strong>
                ₱{{ number_format((float) $order->total, 2) }}
            </p>

            <div class="order-footer">
                <span class="muted">
                    Payment: {{ ucfirst($order->payment_status ?? 'pending') }}
                </span>

                <a class="button"
                   href="{{ route('customer.orders.show', $order) }}">
                    Track Order
                </a>
            </div>
        </section>
    @empty
        <div class="empty">
            <h2>No orders yet</h2>
            <p class="muted">Your orders will appear here after checkout.</p>
            <a class="button" href="{{ route('home') }}">Browse Rice</a>
        </div>
    @endforelse

    {{ $orders->links() }}
</div>
@include('components.smart-assistant')
</body>
</html>
