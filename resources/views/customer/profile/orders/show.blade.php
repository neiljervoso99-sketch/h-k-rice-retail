
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Order | H&K Consumer Goods Store</title>
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

        .container { max-width: 900px; margin: auto; }

        .card {
            background: white;
            border: 1px solid #e1e8e1;
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 18px;
        }

        .muted { color: #68766b; font-size: 14px; }
        a { color: #21683a; text-decoration: none; }

        .status {
            display: inline-block;
            padding: 7px 12px;
            background: #edf4ee;
            color: #21683a;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .cancelled {
            background: #fcebea;
            color: #a52822;
        }

        .timeline {
            border-left: 3px solid #dce6dd;
            margin: 22px 0 8px 8px;
            padding-left: 22px;
        }

        .step {
            position: relative;
            padding: 0 0 25px;
        }

        .step:last-child { padding-bottom: 0; }

        .step::before {
            content: "";
            position: absolute;
            left: -31px;
            top: 3px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #cbd5cc;
            border: 2px solid white;
            box-shadow: 0 0 0 1px #cbd5cc;
        }

        .step.done::before {
            background: #258044;
            box-shadow: 0 0 0 1px #258044;
        }

        .step p { margin: 5px 0 0; }

        .detail {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 11px 0;
            border-bottom: 1px solid #edf0ed;
        }

        .detail:last-child { border-bottom: 0; }

        .detail span:first-child { color: #68766b; }
        .detail span:last-child {
            text-align: right;
            overflow-wrap: anywhere;
        }

        .item {
            padding: 12px 0;
            border-bottom: 1px solid #edf0ed;
        }

        @media (max-width: 600px) {
            body { padding: 14px; }
            .card { padding: 17px; }
            .detail { align-items: flex-start; gap: 8px; }
            .detail span:last-child { max-width: 62%; }
            .container { overflow-wrap: anywhere; }
        }
    </style>
</head>
<body>
<div class="notification-floating">
    @include('components.notification-center')
</div>
<div class="container">
    <p><a href="{{ route('customer.orders.index') }}">← Back to My Orders</a></p>

    <section class="card">
        <p class="muted">ORDER NUMBER</p>
        <h1>{{ $order->order_number ?: 'Order #' . $order->id }}</h1>

        <span class="status {{ $order->status === 'cancelled' ? 'cancelled' : '' }}">
            {{ ucfirst($order->status) }}
        </span>

        <p class="muted">
            Placed {{ $order->created_at->format('M d, Y h:i A') }}
        </p>

        @php
            $steps = [
                'pending' => 'Order Placed',
                'accepted' => 'Order Accepted',
                'completed' => 'Order Completed',
            ];

            $stepOrder = ['pending', 'accepted', 'completed'];

            $currentIndex = array_search($order->status, $stepOrder, true);
        @endphp

        <h2>Order Progress</h2>

        @if ($order->status === 'cancelled')
            <p class="cancelled">This order has been cancelled.</p>
        @else
            <div class="timeline">
                @foreach ($steps as $key => $label)
                    <div class="step {{ $currentIndex !== false && $currentIndex >= array_search($key, $stepOrder, true) ? 'done' : '' }}">
                        <strong>{{ $label }}</strong>

                        @if ($key === 'pending')
                            <p class="muted">Your order has been received.</p>
                        @elseif ($key === 'accepted')
                            <p class="muted">The store has accepted your order.</p>
                        @else
                            <p class="muted">The order has been marked completed.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card">
        <h2>Delivery Information</h2>

        <div class="detail">
            <span>Recipient</span>
            <span>{{ $order->recipient_name }}</span>
        </div>

        <div class="detail">
            <span>Contact number</span>
            <span>{{ $order->phone }}</span>
        </div>

        <div class="detail">
            <span>Address</span>
            <span>
                {{ $order->address_line }},
                {{ $order->barangay }},
                {{ $order->municipality }},
                {{ $order->province }}
                {{ $order->postal_code }}
            </span>
        </div>

        <div class="detail">
            <span>Delivery method</span>
            <span>{{ $order->delivery_method ?: 'Not specified' }}</span>
        </div>

        <div class="detail">
            <span>Delivery area</span>
            <span>{{ $order->delivery_area ?: 'Not specified' }}</span>
        </div>

        <div class="detail">
            <span>Delivery fee</span>
            <span>₱{{ number_format((float) $order->delivery_fee, 2) }}</span>
        </div>

        <div class="detail">
            <span>Assigned rider</span>
            <span>{{ $order->rider?->name ?? 'Not assigned yet' }}</span>
        </div>

        <div class="detail">
            <span>Rider assigned at</span>
            <span>
                {{ $order->assigned_at?->format('M d, Y h:i A') ?? 'Not assigned yet' }}
            </span>
        </div>

        @if ($order->delivery_notes)
            <div class="detail">
                <span>Delivery notes</span>
                <span>{{ $order->delivery_notes }}</span>
            </div>
        @endif
    </section>

    <section class="card">
        <h2>Order Items</h2>

        @forelse ($order->items as $item)
            <div class="item">
                <strong>{{ $item->product_name }}</strong>

                <p class="muted">
                    Quantity: {{ $item->quantity }}
                    {{ $item->unit_type === 'sack' ? 'sack(s)' : 'unit(s)' }}
                    @if ($item->quantity_kg)
                        · {{ $item->quantity_kg }} kg
                    @endif
                </p>

                @if ($order->status === 'completed' && $item->product_id)
                    @if (in_array($item->product_id, $reviewedProductIds, true))
                        <p class="muted">Review submitted. Thanks for your feedback.</p>
                    @else
                        <form method="POST" action="{{ route('customer.orders.reviews.store', [$order, $item->product_id]) }}" enctype="multipart/form-data">
                            @csrf
                            <label for="rating-{{ $item->id }}">Your rating</label>
                            <select id="rating-{{ $item->id }}" name="rating" required>
                                <option value="">Choose stars</option>
                                @for ($rating = 5; $rating >= 1; $rating--)
                                    <option value="{{ $rating }}">{{ $rating }} {{ $rating === 1 ? 'star' : 'stars' }}</option>
                                @endfor
                            </select>
                            <label for="review-{{ $item->id }}">Review (optional)</label>
                            <textarea id="review-{{ $item->id }}" name="review" maxlength="1000" rows="3"></textarea>
                            <label for="review-photo-{{ $item->id }}">Photo (optional)</label>
                            <input id="review-photo-{{ $item->id }}" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
                            <button class="button" type="submit">Submit Review</button>
                        </form>
                    @endif
                @endif
            </div>
        @empty
            <p class="muted">No order items found.</p>
        @endforelse

        <div class="detail">
            <span>Subtotal</span>
            <span>₱{{ number_format((float) $order->subtotal, 2) }}</span>
        </div>

        <div class="detail">
            <span>Delivery fee</span>
            <span>₱{{ number_format((float) $order->delivery_fee, 2) }}</span>
        </div>

        <div class="detail">
            <strong>Total</strong>
            <strong>₱{{ number_format((float) $order->total, 2) }}</strong>
        </div>

        <div class="detail">
            <span>Payment method</span>
            <span>{{ $order->payment_method }}</span>
        </div>

        <div class="detail">
            <span>Payment status</span>
            <span>{{ ucfirst($order->payment_status ?? 'pending') }}</span>
        </div>

        @if ($order->accepted_at)
            <div class="detail">
                <span>Accepted at</span>
                <span>{{ $order->accepted_at->format('M d, Y h:i A') }}</span>
            </div>
        @endif

        @if ($order->completed_at)
            <div class="detail">
                <span>Completed at</span>
                <span>{{ $order->completed_at->format('M d, Y h:i A') }}</span>
            </div>
        @endif

        @if ($order->delivery_proof_uploaded_at)
            <p class="muted">
                Delivery proof uploaded:
                {{ $order->delivery_proof_uploaded_at->format('M d, Y h:i A') }}
            </p>
            <p class="muted">
                Delivery proof has been recorded for this order.
            </p>
        @endif
    </section>

    <form method="POST" action="{{ route('customer.orders.reorder', $order) }}">
        @csrf
        <button class="button" type="submit">Buy Again</button>
    </form>
</div>
@include('components.smart-assistant')
</body>
</html>
