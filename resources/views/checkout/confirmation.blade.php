
<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Confirmation | H&amp;K Consumer Goods Store</title>
     <link rel="icon" type="image/png" href="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}?v=2">

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #fffdf5;
            color: #203329;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        header, footer {
            background: #104b30;
            color: white;
            padding: 18px 0;
        }
        .container { width: min(760px, 92%); margin: auto; }
        main { padding: 32px 0 55px; }
        h1, h2 { color: #104b30; }
        .panel {
            background: white;
            border: 1px solid #e2e9e1;
            border-radius: 14px;
            padding: 22px;
            margin: 18px 0;
        }
        .notice {
            background: #e8f4eb;
            border-radius: 10px;
            padding: 16px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid #e2e9e1;
        }
        .row:last-child { border-bottom: 0; }
        .muted { color: #6d7c72; font-size: 14px; }
        .btn {
            display: inline-block;
            background: #176b42;
            color: white;
            padding: 11px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .thank-you-dialog {
            width: min(440px, calc(100% - 32px));
            border: 0;
            border-radius: 12px;
            padding: 30px 28px;
            color: #203329;
            text-align: center;
            box-shadow: 0 20px 60px rgb(0 0 0 / 25%);
        }
        .thank-you-dialog::backdrop { background: rgb(12 35 23 / 58%); }
        .success-icon {
            display: grid;
            width: 54px;
            height: 54px;
            margin: 0 auto 14px;
            place-items: center;
            border-radius: 50%;
            background: #15803d;
            color: #fff;
            font-size: 32px;
            font-weight: bold;
            line-height: 1;
        }
        .thank-you-dialog h2 { margin: 0 0 12px; color: #15803d; }
        .thank-you-dialog p { margin: 0 0 24px; }
        .thank-you-dialog .dialog-action {
            display: inline-flex;
            width: min(100%, 220px);
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            background: #15803d;
            color: white;
            font: inherit;
            font-weight: bold;
            text-decoration: none;
        }
        .thank-you-dialog .dialog-action:hover {
            background: #166534;
        }
        @media (max-width: 520px) {
            header, footer { padding: 14px 0; }
            main { padding: 22px 0 36px; }
            .panel { padding: 16px; }
            .row { flex-direction: column; gap: 2px; }
            .btn { display: flex; justify-content: center; min-height: 44px; }
            h1 { font-size: 25px; }
        }
    </style>
    @vite('resources/js/app.js')
</head>
<body>
@auth
    <div class="notification-floating">
        @include('components.notification-center')
    </div>
@endauth
<header>
    <div class="container"><strong>H&amp;K Consumer Goods Store</strong></div>
</header>

<main class="container">
    <div class="notice">
        <h1>Order submitted</h1>
        <p>
            Your order record has been created. Please wait for the store
            to confirm product availability and the final amount.
        </p>
    </div>

    <section class="panel">
        <h2>Order details</h2>

        <div class="row">
            <strong>Order number</strong>
            <strong>{{ $order->order_number }}</strong>
        </div>
        <div class="row">
            <span>Order status</span>
            <strong>{{ $order->display_status }}</strong>
        </div>
        <div class="row">
            <span>Payment status</span>
            <strong>{{ ucfirst($order->payment_status) }}</strong>
        </div>
        <div class="row">
            <span>Payment method</span>
            <span>{{ strtoupper($order->payment_method) }}</span>
        </div>
        <div class="row">
            <span>Fulfilment</span>
            <span>{{ ucfirst($order->delivery_method) }}</span>
        </div>
        <div class="row">
            <span>Recipient</span>
            <span>{{ $order->recipient_name }}</span>
        </div>

        @if ($order->delivery_method === 'delivery')
            <div class="row">
                <span>Delivery address</span>
                <span>
                    {{ $order->address_line }},
                    {{ $order->barangay }},
                    {{ $order->municipality }},
                    {{ $order->province }}
                    {{ $order->postal_code }}
                </span>
            </div>
        @endif
    </section>

    <section class="panel">
        <h2>Items ordered</h2>

        @foreach ($order->items as $item)
            <div class="row">
                <div>
                    <strong>{{ $item->product_name }}</strong>
                    <div class="muted">
                        {{ $item->quantity }}
                        {{ $item->quantity_unit ?: $item->unit_type }}

                        @if ($item->unit_type === 'sack' && $item->sack_weight_kg)
                            ({{ $item->sack_weight_kg }} kg each)
                        @endif
                    </div>
                </div>
                <strong>
                    &#8369;{{ number_format((float) $item->line_total, 2) }}
                </strong>
            </div>
        @endforeach

        <div class="row">
            <span>Subtotal</span>
            <strong>
                &#8369;{{ number_format((float) $order->subtotal, 2) }}
            </strong>
        </div>
        <div class="row">
            <span>Delivery fee</span>
            <strong>
                &#8369;{{ number_format((float) $order->delivery_fee, 2) }}
            </strong>
        </div>
        <div class="row">
            <strong>Recorded total</strong>
            <strong>
                &#8369;{{ number_format((float) $order->total, 2) }}
            </strong>
        </div>

        <p class="muted">
            The delivery fee is currently recorded as zero in the system.
            Confirm the final amount with the store before paying.
        </p>
    </section>

    <section class="panel">
        <h2>What happens next?</h2>
        <ul>
            <li>The store reviews the order and product availability.</li>
            <li>The store confirms the final amount and any delivery fee.</li>
            <li>Payment remains pending until it is verified.</li>
        </ul>
        <p class="muted">
            This page confirms the order record only. It does not mean the
            store has accepted the order or received payment.
        </p>
        <a class="btn" href="{{ route('home') }}" target="_top">Return to products</a>
    </section>
</main>

<footer>
    <div class="container">H&amp;K Consumer Goods Store</div>
</footer>
@if (session('order_just_submitted'))
    <dialog class="thank-you-dialog" id="thank-you-dialog" aria-labelledby="thank-you-title">
        <div class="success-icon" aria-hidden="true">✓</div>
        <h2 id="thank-you-title">Success!</h2>
        <p>Your order was successful. The store will review and confirm your order.</p>
        <a class="dialog-action" href="{{ route('home') }}" target="_top">Go back</a>
    </dialog>
    <script>
        const thankYouDialog = document.getElementById('thank-you-dialog');
        thankYouDialog.showModal();
    </script>
@endif
@include('components.smart-assistant')
</body>
</html>
