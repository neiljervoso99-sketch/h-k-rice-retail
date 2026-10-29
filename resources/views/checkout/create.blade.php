<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout | H&amp;K Consumer Goods Store</title>
     <link rel="icon" type="image/png" href="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}?v=2">


    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #fffdf5;
            color: #203329;
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        header, footer {
            background: #104b30;
            color: white;
            padding: 18px 0;
        }

        .container {
            width: min(900px, 92%);
            margin: auto;
        }

        header a {
            color: white;
            text-decoration: none;
        }

        main { padding: 32px 0 55px; }

        h1, h2 { color: #104b30; }

        .panel {
            background: white;
            border: 1px solid #e2e9e1;
            border-radius: 14px;
            padding: 22px;
            margin: 18px 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .full { grid-column: 1 / -1; }

        input, select, textarea, button, .btn {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d5dfd5;
            border-radius: 8px;
            font: inherit;
        }

        textarea {
            min-height: 85px;
            resize: vertical;
        }

        input[type="checkbox"], input[type="radio"] {
            width: auto;
        }

        input[type="file"] { background: #fff; }

        .choice {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid #e2e9e1;
            border-radius: 8px;
            margin: 8px 0;
        }

        .notice {
            padding: 13px 16px;
            background: #e8f4eb;
            border-radius: 8px;
            margin: 14px 0;
        }

        .error {
            background: #fff0e8;
            color: #8c3026;
        }

        .muted {
            color: #6d7c72;
            font-size: 13px;
        }

        .btn {
            display: inline-block;
            background: #176b42;
            color: white;
            border: 0;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        button:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 18px;
        }

        .actions > * { flex: 1; }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid #e2e9e1;
        }

        .summary-row:last-child { border-bottom: 0; }

        .price { white-space: nowrap; }

        .status {
            font-size: 13px;
            color: #6d7c72;
        }

        .final-summary { border: 2px solid #176b42; }

        .final-summary h2 { margin-top: 0; }

        .grand-total {
            font-size: 1.15rem;
            color: #104b30;
        }

        [hidden] { display: none !important; }

        .gcash-payment {
            text-align: center;
            padding: 18px;
            margin-top: 16px;
            background: #f5f9ff;
            border: 1px solid #d9e7fb;
            border-radius: 12px;
        }

       .gcash-qr {
    display: block;
    width: 150px;
    max-width: 100%;
    height: auto;
    margin: 10px auto;
    border-radius: 8px;
}

        .address-suggestions {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-top: 4px;
            max-height: 240px;
            overflow-y: auto;
            position: relative;
            z-index: 20;
        }

        .address-suggestions [role="option"] {
            cursor: pointer;
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }

        .address-suggestions [role="option"]:hover,
        .address-suggestions [role="option"]:focus {
            background: #f2f2f2;
        }

        @media (max-width: 600px) {
            .grid { grid-template-columns: 1fr; }
            .full { grid-column: auto; }
            .actions { flex-direction: column; }
            .actions > * { width: 100%; min-height: 44px; }
            .summary-row {
                flex-direction: column;
                gap: 2px;
            }
            .price { white-space: normal; }
            input:not([type="checkbox"]):not([type="radio"]),
            select,
            textarea {
                min-height: 44px;
                font-size: 16px;
            }
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
    <div class="container">
        <strong>H&amp;K Consumer Goods Store</strong> &nbsp; · &nbsp;
        <a href="{{ route('cart.index') }}">Back to cart</a>
    </div>
</header>

<main class="container">
    <h1>Checkout</h1>
    <p>Review your cart and provide your contact and order details.</p>

    @if ($errors->any())
        <div class="notice error">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('cart_error'))
        <div class="notice error">
            {{ session('cart_error') }}
        </div>
    @endif

    @if ($needsPriceConfirmation || $hasUnavailableStock)
        <div class="notice error">
            Checkout is unavailable because one or more items need price
            confirmation, have insufficient stock, or need to be corrected.
            <a href="{{ route('cart.index') }}">Return to your cart</a>.
        </div>
    @else
        <div class="notice">
            Your order will remain pending until the store reviews it.
            Submitting an order does not verify payment or deduct stock.
        </div>
    @endif

    @php
        $zoneLabels = [
            'abuyog_proper' =>
                'Proper Abuyog — ₱50 up to 2 sacks; ₱100 for 3+',
            'central_nearby' =>
                'Central and nearby — ₱250 for 1–5 sacks; ₱500 for 6+',
            'medium' =>
                'Other medium-fee areas — ₱250 for 1–5 sacks; ₱500 for 6+',
            'far' =>
                'Far areas — ₱250 for 1–5 sacks; ₱500 for 6+',
        ];
    @endphp

    <form
        method="POST"
        action="{{ route('checkout.store') }}"
        enctype="multipart/form-data"
        autocomplete="on"
    >
        @csrf

        <section class="panel">
            <h2>Contact and delivery address</h2>

            <div class="grid">
                <div class="field">
                    <label for="recipient_name">Recipient name</label>
                    <input
                        id="recipient_name"
                        name="recipient_name"
                        value="{{ old('recipient_name', auth()->user()->name ?? '') }}"
                        required
                        maxlength="150"
                        autocomplete="name">
                </div>

                <div class="field">
                    <label for="phone">Mobile number</label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone') }}"
                        required
                        maxlength="30"
                        autocomplete="tel"
                        placeholder="Contact number">
                </div>

                <div class="field full">
                    <label>Fulfilment method</label>

                    <label class="choice">
                        <input
                            type="radio"
                            name="delivery_method"
                            value="delivery"
                            @checked(old('delivery_method', 'delivery') === 'delivery')
                            required>
                        Delivery
                    </label>

                    <label class="choice">
                        <input
                            type="radio"
                            name="delivery_method"
                            value="pickup"
                            @checked(old('delivery_method') === 'pickup')>
                        Store pickup
                    </label>
                </div>

                <div class="field full" id="barangay-field">
                    <label for="address_line">Delivery address and barangay</label>

                    <select id="barangay" name="barangay" hidden>
                        <option value="">Select barangay</option>

                        @foreach ($barangayZones as $name => $zone)
                            <option
                                value="{{ $name }}"
                                @selected(old('barangay') === $name)
                            >{{ $name }}</option>
                        @endforeach
                    </select>

                    <p class="muted">
                        Type your address or barangay, then choose the matching barangay suggestion.
                        The delivery fee and total update automatically.
                    </p>
                </div>

                <div class="field full" id="address-field">
                    <input
                        id="address_line"
                        name="address_line"
                        value="{{ old('address_line') }}"
                        maxlength="255"
                        autocomplete="street-address"
                        placeholder="Enter your complete address">

                    <div id="address-suggestions"
                         class="address-suggestions"
                         role="listbox"
                         hidden></div>
                </div>

                <div class="field">
                    <label for="municipality">Municipality / City</label>
                    <input
                        id="municipality"
                        name="municipality"
                        value="{{ old('municipality', 'Abuyog') }}"
                        maxlength="120"
                        required>
                </div>

                <div class="field">
                    <label for="province">Province</label>
                    <input
                        id="province"
                        name="province"
                        value="{{ old('province', 'Leyte') }}"
                        maxlength="120"
                        required>
                </div>

                <div class="field">
                    <label for="postal_code">Postal code (optional)</label>
                    <input
                        id="postal_code"
                        name="postal_code"
                        value="{{ old('postal_code') }}"
                        maxlength="10"
                        autocomplete="postal-code">
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Payment method</h2>

            <label class="choice">
                <input
                    type="radio"
                    name="payment_method"
                    value="cash"
                    @checked(old('payment_method', 'cash') === 'cash')
                    required>
                Cash on delivery / cash on pickup
            </label>

            <label class="choice">
                <input
                    type="radio"
                    name="payment_method"
                    value="gcash"
                    @checked(old('payment_method') === 'gcash')>
                GCash
            </label>

            <div id="gcash-proof-fields" hidden>
                <div class="gcash-payment">
                    <h3>Pay using GCash</h3>

                    <p>
                        Scan the QR code below using your GCash app.
                        Please verify the recipient and amount before sending.
                    </p>

                    <img
                        class="gcash-qr"
                        src="{{ asset('images/payments/gcash-qr code.jpg') }}"
                        alt="H&amp;K Consumer Goods Store GCash payment QR code">

                    <p class="muted">
                        Only pay after the store confirms your final order
                        total. Keep your transaction receipt after payment.
                    </p>
                </div>

                <div class="field" style="margin-top: 16px;">
                    <label for="gcash_reference_number">
                        GCash Reference Number
                    </label>

                    <input
                        id="gcash_reference_number"
                        name="gcash_reference_number"
                        type="text"
                        value="{{ old('gcash_reference_number') }}"
                        maxlength="100"
                        autocomplete="off"
                        placeholder="Enter your GCash transaction reference"
                        disabled>

                    <p class="muted">
                        Enter the reference number shown in your completed
                        GCash transaction.
                    </p>
                </div>

                <div class="field" style="margin-top: 16px;">
                    <label for="payment_proof">Payment Screenshot</label>

                    <input
                        id="payment_proof"
                        name="payment_proof"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        disabled>

                    <p class="muted">
                        Upload a clear screenshot of the completed payment.
                        Accepted formats: JPG, PNG, or WebP. Maximum file
                        size: 5 MB.
                    </p>
                </div>
            </div>

            <p class="muted">
                GCash is not marked as paid just because you select it.
                The store must verify your payment.
            </p>
        </section>

        <section class="panel">
            <h2>Receipt and additional notes</h2>

            <label class="choice">
                <input
                    type="checkbox"
                    name="receipt_requested"
                    value="1"
                    @checked(old('receipt_requested'))>
                I would like to request a receipt for this order.
            </label>

            <div class="field">
                <label for="customer_notes">Order notes (optional)</label>
                <textarea
                    id="customer_notes"
                    name="customer_notes"
                    maxlength="1000"
                    placeholder="Delivery instructions or other requests">{{ old('customer_notes') }}</textarea>
            </div>
        </section>

        <section class="panel final-summary">
            <h2>Final order summary</h2>

            @forelse ($items as $item)
                <div class="summary-row">
                    <div>
                        <strong>{{ $item['product']->name }}</strong>

                        <div class="status">
                            @php
                                $isGenericItem = $item['is_generic']
                                    ?? (($item['product']->inventory_type ?? 'rice') === 'generic');
                            @endphp

                            {{ number_format((float) $item['quantity'], 2) }}
                            {{ $isGenericItem
                                ? ($item['product']->stock_unit ?? $item['unit'])
                                : ($item['unit'] === 'kg' ? 'kg' : 'sack') }}

                            @if (
                                !$isGenericItem
                                && $item['unit'] === 'sack'
                                && $item['product']->sack_weight_kg
                            )
                                ({{ $item['product']->sack_weight_kg }} kg each)
                            @endif
                        </div>

                        @if (!$item['confirmed'])
                            <div class="status">
                                Price requires store confirmation.
                            </div>
                        @elseif (!$item['in_stock'] || !$item['minimum_met'])
                            <div class="status">
                                Please update this item in your cart.
                            </div>
                        @endif
                    </div>

                    <div class="price">
                        @if ($item['subtotal'] !== null)
                            &#8369;{{ number_format($item['subtotal'], 2) }}
                        @else
                            Price pending
                        @endif
                    </div>
                </div>
            @empty
                <p>Your cart has no available items.</p>
            @endforelse

            <div class="summary-row">
                <strong>Actual sack count</strong>
                <strong>{{ $totalSacks }}</strong>
            </div>

            <div class="summary-row">
                <strong>Subtotal</strong>
                <strong class="price">
                    @if (!$needsPriceConfirmation)
                        &#8369;{{ number_format($subtotal, 2) }}
                    @else
                        Pending price confirmation
                    @endif
                </strong>
            </div>

            <div class="summary-row">
                <span>Estimated delivery fee</span>
                <strong class="price" id="delivery-fee-display">
                    Select a delivery method
                </strong>
            </div>

            <div class="summary-row">
                <strong class="grand-total">Estimated total</strong>
                <strong class="price grand-total" id="total-display">
                    @if (!$needsPriceConfirmation)
                        &#8369;{{ number_format($subtotal, 2) }}
                    @else
                        Pending price confirmation
                    @endif
                </strong>
            </div>

            <p class="muted" id="fee-explanation">
                Enter your delivery address and choose your barangay suggestion
                to calculate the delivery fee and estimated total.
            </p>

            <p class="muted">
                These amounts are estimates. Wait for store confirmation
                before making any payment.
            </p>
        </section>

        <div class="actions">
            <a class="btn" href="{{ route('cart.index') }}">
                Back to cart
            </a>

            <button
                id="submit-order"
                class="btn"
                type="submit"
                data-can-proceed="{{ $canProceed ? '1' : '0' }}"
                @disabled(!$canProceed)>
                {{ $canProceed ? 'Submit order for confirmation' : 'Checkout unavailable' }}
            </button>
        </div>
    </form>
</main>

<footer>
    <div class="container">H&amp;K Consumer Goods Store</div>
</footer>

<script>
(() => {
    const deliveryRadios = document.querySelectorAll(
        'input[name="delivery_method"]'
    );

    const barangay = document.getElementById('barangay');
    const address = document.getElementById('address_line');
    const addressSuggestions = document.getElementById('address-suggestions');
    const municipality = document.getElementById('municipality');
    const province = document.getElementById('province');

    const barangayField = document.getElementById('barangay-field');
    let addressSearchTimer = null;
    let addressSearchController = null;

    function closeAddressSuggestions() {
        addressSuggestions.replaceChildren();
        addressSuggestions.hidden = true;
    }

    function showAddressSuggestions(items) {
        closeAddressSuggestions();

        const query = address.value.trim().toLowerCase();

        if (!query) {
            return;
        }

        const localMatches = Array.from(barangay.options)
            .filter(option => option.value)
            .filter(option =>
                option.value.toLowerCase().includes(query)
            )
            .map(option => ({
                type: 'barangay',
                name: option.value,
                label: option.value
            }));

        const seen = new Set();

        const uniqueLocalMatches = localMatches.filter(item => {
            const key = item.name.toLowerCase();

            if (seen.has(key)) {
                return false;
            }

            seen.add(key);
            return true;
        });

        const suggestions = [
            ...uniqueLocalMatches,
            ...items.map(item => ({
                type: 'address',
                ...item,
                label: item.formatted
            }))
        ];

        if (!suggestions.length) {
            return;
        }

        suggestions.forEach(item => {
            const option = document.createElement('div');

            option.setAttribute('role', 'option');
            option.setAttribute('tabindex', '0');
            option.textContent = item.type === 'barangay'
                ? `${item.label} — Barangay`
                : item.label;

            const choose = () => {
                if (item.type === 'barangay') {
                    barangay.value = item.name;
                    address.value = item.name;
                } else {
                    address.value = item.formatted || '';

                    if (item.city) {
                        municipality.value = item.city;
                    }

                    if (item.province) {
                        province.value = item.province;
                    }

                    const possibleBarangay = [
                        item.suburb,
                        item.village
                    ]
                        .filter(value => typeof value === 'string')
                        .map(value => value.trim().toLowerCase())
                        .find(value =>
                            Array.from(barangay.options).some(option =>
                                option.value.toLowerCase() === value
                            )
                        );

                    const matchingOption = possibleBarangay
                        ? Array.from(barangay.options).find(option =>
                            option.value.toLowerCase() === possibleBarangay
                        )
                        : null;

                    barangay.value = matchingOption
                        ? matchingOption.value
                        : '';
                }

                closeAddressSuggestions();

                barangay.dispatchEvent(new Event('change', {
                    bubbles: true
                }));

                address.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            };

            option.addEventListener('click', choose);

            option.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    choose();
                }

                if (event.key === 'Escape') {
                    closeAddressSuggestions();
                    address.focus();
                }
            });

            addressSuggestions.appendChild(option);
        });

        addressSuggestions.hidden = false;
    }

    address.addEventListener('input', () => {
        clearTimeout(addressSearchTimer);

        if (addressSearchController) {
            addressSearchController.abort();
        }

        barangay.value = '';
        updateCheckout();

        const text = address.value.trim();

        if (text.length < 3) {
            showAddressSuggestions([]);
            return;
        }

        showAddressSuggestions([]);

        addressSearchTimer = setTimeout(async () => {
            addressSearchController = new AbortController();

            try {
                const response = await fetch(
                    @json(route('address.suggestions')),
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'input[name="_token"]'
                            ).value
                        },
                        body: JSON.stringify({ text }),
                        signal: addressSearchController.signal
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    return;
                }

                const items = (data.suggestions || []).filter((item) => {
                    const city = String(item.city || '').toLowerCase();
                    const provinceName = String(item.province || '').toLowerCase();
                    const country = String(item.country || '').toLowerCase();

                    return city.includes('abuyog')
                        && provinceName.includes('leyte')
                        && country === 'philippines';
                });

                if (address.value.trim() === text) {
                    showAddressSuggestions(items);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Address suggestions request failed.', error);
                }
            }
        }, 350);
    });

    address.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAddressSuggestions();
        }
    });

    document.addEventListener('click', (event) => {
        if (
            !addressSuggestions.contains(event.target)
            && event.target !== address
        ) {
            closeAddressSuggestions();
        }
    });

    const addressField = document.getElementById('address-field');
    const feeDisplay = document.getElementById('delivery-fee-display');
    const totalDisplay = document.getElementById('total-display');
    const explanation = document.getElementById('fee-explanation');
    const submitButton = document.getElementById('submit-order');

    const canProceed = submitButton.dataset.canProceed === '1';
    const sackCount = Number(@json($totalSacks));
    const subtotal = @json(
        $needsPriceConfirmation ? null : round($subtotal, 2)
    );

    const zones = @json($barangayZones);

    const money = (amount) => '\u20B1' + Number(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function calculateFee(method, selectedBarangay) {
        if (method === 'pickup') {
            return {
                fee: 0,
                eligible: true,
                text: 'Store pickup has no delivery fee.'
            };
        }

        if (method !== 'delivery') {
            return {
                fee: null,
                eligible: false,
                text: 'Select a delivery method to calculate the fee.'
            };
        }

        if (!selectedBarangay) {
            return {
                fee: null,
                eligible: false,
                text: 'Choose your barangay to calculate the delivery fee.'
            };
        }

        if (!Object.prototype.hasOwnProperty.call(zones, selectedBarangay)) {
            return {
                fee: null,
                eligible: false,
                text: 'The selected barangay is not configured for delivery.'
            };
        }

        const zone = zones[selectedBarangay];

        if (zone === 'abuyog_proper') {
            const fee = sackCount <= 2 ? 50 : 100;

            return {
                fee,
                eligible: true,
                text: sackCount <= 2
                    ? 'Proper Abuyog rate: ₱50 for up to 2 sacks.'
                    : 'Proper Abuyog rate: ₱100 for 3 or more sacks.'
            };
        }

        if (
            zone === 'central_nearby' ||
            zone === 'medium' ||
            zone === 'far'
        ) {
            const hasGenericItems = @json(
                collect($items)->contains(fn ($item) => $item['is_generic'] ?? false)
            );

            if (sackCount < 1 && !hasGenericItems) {
                return {
                    fee: null,
                    eligible: false,
                    text: 'Delivery to this area requires at least one sack of rice.'
                };
            }

            const fee = sackCount >= 6 ? 500 : 250;

            return {
                fee,
                eligible: true,
                text: sackCount >= 6
                    ? 'Delivery rate: ₱500 for 6 or more rice sacks.'
                    : 'Delivery rate: ₱250 for 1–5 rice sacks or generic-only orders.'
            };
        }

        return {
            fee: null,
            eligible: false,
            text: 'The delivery fee could not be calculated.'
        };
    }

    function updateCheckout() {
        const selected = document.querySelector(
            'input[name="delivery_method"]:checked'
        );

        const method = selected ? selected.value : '';
        const isDelivery = method === 'delivery';

        barangayField.hidden = !isDelivery;
        addressField.hidden = !isDelivery;

        barangay.required = false;
        address.required = isDelivery;

        if (!isDelivery) {
            barangay.value = '';
        }

        const result = calculateFee(method, barangay.value);

        if (result.fee === null) {
            feeDisplay.textContent =
                isDelivery && barangay.value
                    ? 'Not available'
                    : 'Select a delivery method';
        } else {
            feeDisplay.textContent = money(result.fee);
        }

        explanation.textContent = result.text;

        if (subtotal === null) {
            totalDisplay.textContent = 'Pending price confirmation';
        } else if (result.fee === null) {
            totalDisplay.textContent = 'Not available';
        } else {
            totalDisplay.textContent = money(subtotal + result.fee);
        }

        submitButton.disabled =
            !canProceed ||
            !result.eligible ||
            (isDelivery && !barangay.value);
    }

    const paymentRadios = document.querySelectorAll(
        'input[name="payment_method"]'
    );

    const gcashFields = document.getElementById('gcash-proof-fields');
    const gcashReference = document.getElementById('gcash_reference_number');
    const paymentProof = document.getElementById('payment_proof');

    function updatePaymentFields() {
        const selectedPayment = document.querySelector(
            'input[name="payment_method"]:checked'
        );

        const isGcash = selectedPayment?.value === 'gcash';

        gcashFields.hidden = !isGcash;
        gcashReference.disabled = !isGcash;
        paymentProof.disabled = !isGcash;

        gcashReference.required = isGcash;
        paymentProof.required = isGcash;
    }

    deliveryRadios.forEach((radio) => {
        radio.addEventListener('change', updateCheckout);
    });

    barangay.addEventListener('change', updateCheckout);

    paymentRadios.forEach((radio) => {
        radio.addEventListener('change', updatePaymentFields);
    });

    updateCheckout();
    updatePaymentFields();
})();
</script>
@include('components.smart-assistant')
</body>
</html>