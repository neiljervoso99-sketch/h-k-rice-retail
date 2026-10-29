<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Details | H&K Consumer Goods Store</title>
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

        h2 {
            font-size: 17px;
            color: #245b35;
            margin: 0 0 14px;
        }

        h3 {
            font-size: 15px;
            margin: 0 0 8px;
        }

        p {
            font-size: 13px;
            line-height: 1.5;
            margin: 8px 0;
        }

        .button {
            display: inline-block;
            padding: 9px 14px;
            margin: 0 6px 8px 0;
            background: #24683a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 7px;
            transition: background 0.2s ease;
        }

        .button:hover {
            background: #194d2a;
            color: #ffffff;
        }

        .section {
            background: #ffffff;
            border-radius: 10px;
            padding: 17px;
            margin: 14px 0;
            box-shadow: 0 2px 8px #0000000d;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 12px;
        }

        .order-number {
            color: #245b35;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #e5eee5;
            color: #245b35;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            padding: 9px 0;
            border-bottom: 1px solid #edf0eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #66736a;
            flex: 0 0 40%;
        }

        .value {
            text-align: right;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .muted {
            color: #66736a;
        }

        .item {
            padding: 12px 0;
            border-bottom: 1px solid #edf0eb;
        }

        .item:last-child {
            border-bottom: none;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #dce5da;
            font-size: 15px;
            font-weight: 700;
            color: #245b35;
        }

        .completed-box {
            background: #eaf5eb;
            border: 1px solid #cce4ce;
            border-radius: 8px;
            padding: 14px;
        }

        .completed-box h3 {
            color: #245b35;
        }

        .empty {
            color: #66736a;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            h1 {
                font-size: 21px;
            }

            .section {
                padding: 14px;
            }

            .info-row {
                gap: 10px;
            }

            .label {
                flex-basis: 42%;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 44px;
                margin-bottom: 8px;
            }

            .info-row,
            .total-row {
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

    <div>
        <a href="{{ url('/') }}" class="button">
            ← Back to Home
        </a>

        <a href="{{ route('customer.orders.index') }}" class="button">
            Back to My Orders
        </a>
    </div>

    <h1>Order Details</h1>

    <div class="section">
        <div class="order-header">
            <div>
                <p class="order-number">
                    {{ $order->order_number ?? ('Order #' . $order->id) }}
                </p>

                <p class="muted">
                    Placed:
                    {{ $order->created_at?->format('M d, Y - h:i A') ?? 'N/A' }}
                </p>
            </div>

            <span class="status">
                {{ $order->display_status }}
            </span>
        </div>

        @php
            $trackingStages = [
                ['label' => 'Pending', 'at' => $order->created_at],
                ['label' => 'Processing', 'at' => $order->processing_at],
                ['label' => 'Shipped', 'at' => $order->shipped_at],
                ['label' => 'Delivered', 'at' => $order->delivered_at],
            ];
        @endphp

        <div class="info-row">
            <span class="label">Order timeline</span>
            <span class="value">
                @foreach ($trackingStages as $stage)
                    <span style="display:block;">
                        {{ $stage['label'] }}: {{ $stage['at']?->format('M d, Y h:i A') ?? 'Not yet' }}
                    </span>
                @endforeach
                @if ($order->cancelled_at)
                    <span style="display:block;">Cancelled: {{ $order->cancelled_at->format('M d, Y h:i A') }}</span>
                @endif
            </span>
        </div>
    </div>

    {{-- Delivery Information --}}
    <div class="section">
        <h2>Delivery Information</h2>

        <div class="info-row">
            <span class="label">Recipient Name</span>
            <span class="value">
                {{ $order->recipient_name ?? 'Not available' }}
            </span>
        </div>

        <div class="info-row">
            <span class="label">Phone Number</span>
            <span class="value">
                {{ $order->phone ?? 'Not available' }}
            </span>
        </div>

        <div class="info-row">
            <span class="label">Delivery Address</span>
            <span class="value">
                {{ $order->address_line ?? '' }}
                {{ $order->barangay ? ', ' . $order->barangay : '' }}
                {{ $order->municipality ? ', ' . $order->municipality : '' }}
                {{ $order->province ? ', ' . $order->province : '' }}
                {{ $order->postal_code ? ' ' . $order->postal_code : '' }}
            </span>
        </div>

        <div class="info-row">
            <span class="label">Delivery Method</span>
            <span class="value">
                {{ ucfirst($order->delivery_method ?? 'Not available') }}
            </span>
        </div>

        @if (!empty($order->delivery_area))
            <div class="info-row">
                <span class="label">Delivery Area</span>
                <span class="value">
                    {{ $order->delivery_area }}
                </span>
            </div>
        @endif
    </div>

    {{-- Rider Information --}}
    <div class="section">
        <h2>Rider Information</h2>

        @if ($order->rider)
            <div class="info-row">
                <span class="label">Rider Name</span>
                <span class="value">
                    {{ $order->rider->name ?? 'Not available' }}
                </span>
            </div>

            @if (!empty($order->rider->phone))
                <div class="info-row">
                    <span class="label">Phone Number</span>
                    <span class="value">
                        {{ $order->rider->phone }}
                    </span>
                </div>
            @endif

            <p class="muted">
                A rider has been assigned to deliver your order.
            </p>
        @else
            <p class="muted">
                No rider has been assigned to this order yet.
            </p>
        @endif
    </div>

    {{-- Payment Information --}}
    <div class="section">
        <h2>Payment Information</h2>

        <div class="info-row">
            <span class="label">Payment Method</span>
            <span class="value">
                {{ strtoupper($order->payment_method ?? 'Not available') }}
            </span>
        </div>

        <div class="info-row">
            <span class="label">Payment Status</span>
            <span class="value">
                {{ ucfirst(str_replace('_', ' ', $order->payment_status ?? 'pending')) }}
            </span>
        </div>

        <div class="info-row">
            <span class="label">Subtotal</span>
            <span class="value">
                ₱{{ number_format((float) ($order->subtotal ?? 0), 2) }}
            </span>
        </div>

        <div class="info-row">
            <span class="label">Delivery Fee</span>
            <span class="value">
                ₱{{ number_format((float) ($order->delivery_fee ?? 0), 2) }}
            </span>
        </div>

        <div class="total-row">
            <span>Total Amount</span>
            <span>
                ₱{{ number_format((float) ($order->total ?? 0), 2) }}
            </span>
        </div>
    </div>

    {{-- Ordered Items --}}
    <div class="section">
        <h2>Ordered Items</h2>

        @forelse ($order->items as $item)
            <div class="item">
                <h3>
                    {{ $item->product_name ?? $item->name ?? 'Rice product' }}
                </h3>

                <p>
                    <strong>Quantity:</strong>
                    {{ $item->quantity ?? 0 }}
                    {{ $item->unit_type ?? '' }}
                </p>

                <p>
                    <strong>Price:</strong>
                    ₱{{ number_format((float) ($item->price ?? $item->unit_price ?? 0), 2) }}
                </p>

                <p>
                    <strong>Subtotal:</strong>
                    ₱{{ number_format((float) ($item->subtotal ?? 0), 2) }}
                </p>
            </div>
        @empty
            <p class="empty">No ordered items found.</p>
        @endforelse
    </div>

    {{-- Customer Notes --}}
    @if (!empty($order->customer_notes))
        <div class="section">
            <h2>Order Notes</h2>
            <p>{{ $order->customer_notes }}</p>
        </div>
    @endif

    {{-- Order Completed --}}
    @if (($order->status ?? '') === 'completed')
        <div class="section">
            <div class="completed-box">
                <h3>Order Completed</h3>

                <p>
                    Your order has been marked as completed.
                </p>

                @if ($order->completed_at)
                    <p>
                        <strong>Completed on:</strong>
                        {{ $order->completed_at->format('M d, Y - h:i A') }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    <div style="margin-top: 18px;">
        <a href="{{ url('/') }}" class="button">
            ← Back to Home
        </a>

        <a href="{{ route('customer.orders.index') }}" class="button">
            Back to My Orders
        </a>
    </div>

</div>
</body>
</html>