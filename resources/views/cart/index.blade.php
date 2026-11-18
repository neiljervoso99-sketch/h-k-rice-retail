<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shopping Cart | H&K Consumer Goods</title>
    <link rel="icon" type="image/png" href="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}?v=2">
    <style>
        :root {
            --green: #236b3b;
            --green-dark: #174b2a;
            --border: #dce5dc;
            --text: #243126;
            --muted: #68756a;
            --red: #a52d2d;
            --orange: #fff5df;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f7f9f6;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.5;
        }
        header { padding: 18px 20px; color: white; background: var(--green-dark); }
        .header-inner, main, footer { width: min(100% - 32px, 1050px); margin: auto; }
        .brand { font-weight: 800; font-size: 1.2rem; }
        main { padding-top: 30px; padding-bottom: 40px; }
        h1 { margin: 0 0 8px; font-size: clamp(1.7rem, 4vw, 2.2rem); }
        h2 { margin-top: 0; font-size: 1.25rem; }
        h3 { margin: 0 0 4px; font-size: 1.15rem; }
        .muted { color: var(--muted); font-size: .92rem; }
        .intro { margin: 0 0 22px; color: var(--muted); }
        .notice {
            padding: 12px 14px; margin: 12px 0;
            border: 1px solid #b7d8bc; border-radius: 9px;
            background: #eff9ef;
        }
        .notice.error { border-color: #e5b6b6; color: var(--red); background: #fff0f0; }
        .notice.warning { border-color: #e9d19b; background: var(--orange); }
        .item, .summary {
            padding: 20px; margin-bottom: 16px;
            border: 1px solid var(--border); border-radius: 14px;
            background: white; box-shadow: 0 3px 12px #18351a08;
        }
        .item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 22px; }
        .item-details { min-width: 0; }
        .price { min-width: 145px; text-align: right; font-weight: 700; font-size: 1.12rem; }
        .price .muted { margin-top: 5px; font-weight: 400; font-size: .9rem; }
        .stock-available { color: var(--green); font-weight: 700; }
        .stock-warning { color: var(--red); font-weight: 700; }
        .controls {
            display: flex; flex-wrap: wrap; align-items: end;
            gap: 12px; margin-top: 16px;
        }
        label { display: grid; gap: 6px; font-size: .9rem; font-weight: 700; }
        input, select {
            width: 100%; min-height: 42px; padding: 9px 11px;
            border: 1px solid #cbd7ca; border-radius: 8px;
            background: white; color: var(--text); font: inherit;
        }
        .controls label { min-width: 155px; }
        .controls input { width: 110px; }
        button, .button {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 42px; padding: 10px 16px; border: 0;
            border-radius: 8px; background: var(--green); color: white;
            font: inherit; font-weight: 700; text-decoration: none; cursor: pointer;
        }
        button:hover, .button:hover { background: var(--green-dark); }
        button.remove { background: white; color: var(--red); border: 1px solid #e2bcbc; }
        button.remove:hover { background: #fff0f0; }
        .summary { margin-top: 24px; }
        .add-ons {
            padding: 20px;
            margin-top: 20px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #f0f6ef;
        }
        .add-on-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
            gap: 12px;
        }
        .add-on-item {
            display: grid;
            grid-template-columns: 58px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            min-width: 0;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
        }
        .add-on-item img,
        .add-on-image-placeholder {
            display: grid;
            width: 58px;
            height: 58px;
            place-items: center;
            border-radius: 6px;
            background: #edf3eb;
            color: var(--muted);
            object-fit: cover;
            font-size: .75rem;
        }
        .add-on-details { min-width: 0; }
        .add-on-details h3 { overflow-wrap: anywhere; }
        .add-on-details .muted { margin: 3px 0; }
        .add-on-item form { grid-column: 1 / -1; }
        .add-on-item button { width: 100%; }
        .total { font-size: 1.9rem; font-weight: 800; margin: 6px 0 12px; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .button.secondary { color: var(--green-dark); background: white; border: 1px solid var(--border); }
        .button.disabled { opacity: .5; pointer-events: none; }
        .product-picker {
            width: min(940px, calc(100% - 28px));
            max-width: none;
            max-height: min(85vh, 760px);
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--text);
            box-shadow: 0 18px 60px #10201555;
        }
        .product-picker::backdrop { background: #10201599; }
        .product-picker-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }
        .product-picker-header h2 { margin: 0 0 4px; }
        .product-picker-header p { margin: 0; }
        .product-picker-close {
            min-width: 42px;
            padding: 8px;
            background: white;
            color: var(--text);
            border: 1px solid var(--border);
            font-size: 1.25rem;
        }
        .product-picker-close:hover { background: #f0f6ef; }
        .product-picker-search { margin-bottom: 16px; }
        .product-picker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
            gap: 12px;
            max-height: 60vh;
            overflow-y: auto;
            padding: 2px;
        }
        .product-picker-card {
            display: grid;
            grid-template-columns: 58px minmax(0, 1fr);
            align-content: start;
            gap: 10px 12px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #fff;
        }
        .product-picker-card[hidden] { display: none; }
        .product-picker-card img,
        .product-picker-card .add-on-image-placeholder {
            width: 58px;
            height: 58px;
        }
        .product-picker-card form,
        .product-picker-card button { grid-column: 1 / -1; width: 100%; }
        .product-picker-card h3 { overflow-wrap: anywhere; }
        .product-picker-empty { grid-column: 1 / -1; }
        .empty {
            padding: 32px 20px; border: 1px dashed #cbd7ca;
            border-radius: 14px; background: white; text-align: center;
        }
        footer { padding: 18px 0 28px; text-align: center; color: var(--muted); font-size: .9rem; }
        @media (max-width: 640px) {
            header {
                padding: 14px 0;
            }
            .header-inner {
                display: grid;
                gap: 4px;
            }
            .item { grid-template-columns: 1fr; gap: 14px; padding: 16px; }
            .price { text-align: left; min-width: 0; }
            .controls { align-items: stretch; }
            .controls label { flex: 1 1 100%; min-width: 0; }
            .controls input, .controls select { width: 100%; }
            .controls button { width: 100%; }
            .actions { flex-direction: column; }
            .actions .button { width: 100%; }
            input, select, button, .button { min-height: 44px; }
            .total { font-size: 1.6rem; overflow-wrap: anywhere; }
        }
    </style>
    @vite('resources/js/app.js')
</head>
<body data-cart-count="{{ collect(session('cart', []))->sum(fn ($item) => (float) ($item['quantity'] ?? 0)) }}">
@auth
    <div class="notification-floating">
        @include('components.notification-center')
    </div>
@endauth
<header>
    <div class="header-inner">
        <div class="brand">H&K Consumer Goods</div>
        <div>H+K Consumer Goods Store</div>
    </div>
</header>

<main>
    <h1>Your Shopping Cart</h1>
    <p class="intro">Review your products, quantities, prices, and stock availability.</p>

    @if (session('cart_success'))
        <div class="notice" role="status">{{ session('cart_success') }}</div>
    @endif
    @if (session('cart_error'))
        <div class="notice error" role="alert">{{ session('cart_error') }}</div>
    @endif
    @if ($errors->any())
        <div class="notice error" role="alert">
            <strong>Please check your selections:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if (count($items))
        @foreach ($items as $item)
            @php
                $product = $item['product'];
                $isGeneric = ($item['is_generic'] ?? (($product->inventory_type ?? 'rice') === 'generic'));
                $unit = $item['unit'] ?? ($isGeneric ? ($product->stock_unit ?? 'piece') : 'kg');
                $stockUnit = $product->stock_unit ?? 'kg';
                $stock = $isGeneric ? (float) ($product->stock_quantity ?? 0) : (float) ($product->stock_kg ?? 0);
                $displayUnit = $isGeneric ? $stockUnit : 'kg';
                $unitLabel = fn ($value) => match ($value) {
                    'kg' => 'kg',
                    'gram' => 'g',
                    'liter' => 'L',
                    'ml' => 'mL',
                    default => $value,
                };
            @endphp

            <section class="item">
                <div class="item-details">
                    <h3>{{ $product->name }}</h3>
                    <div class="muted">{{ $product->brand ?: 'H+K Consumer Goods' }}</div>

                    <p>
                        @if ($item['in_stock'])
                            <span class="stock-available">Available for this quantity</span>
                        @else
                            <span class="stock-warning">Insufficient or unavailable stock</span>
                        @endif
                        <br>
                        <span class="muted">
                            Recorded inventory:
                            {{ number_format($stock, 2) }} {{ $unitLabel($displayUnit) }}
                        </span>
                    </p>

                    @if (!$isGeneric && $unit === 'kg' && !($item['minimum_met'] ?? false))
                        <div class="notice warning">Minimum order is 1 kg for rice purchases per kilogram.</div>
                    @endif

                    @if (!$item['confirmed'])
                        <div class="notice error">
                            The price is not confirmed. This product cannot proceed to checkout until the store verifies it.
                        </div>
                    @endif

                    @if (!$isGeneric && $unit === 'sack' && !($item['requested_kg'] ?? null))
                        <div class="notice error">The sack weight is not configured. Please contact the store.</div>
                    @endif

                    <form class="controls cart-update-form" method="POST" action="{{ route('cart.update', $product) }}">
                        @csrf
                        @method('PATCH')

                        <label>
                            {{ $isGeneric ? 'Unit' : 'Package' }}
                            <select name="unit" class="unit-select" data-generic="{{ $isGeneric ? '1' : '0' }}" required>
                                @if ($isGeneric)
                                    <option value="{{ $stockUnit }}" selected>{{ ucfirst($stockUnit) }}</option>
                                @else
                                    <option value="kg" @selected($unit === 'kg')>Per kilogram</option>
                                    <option value="sack" @selected($unit === 'sack')>
                                        {{ $product->sack_weight_kg ? $product->sack_weight_kg . ' kg sack' : 'Sack (weight required)' }}
                                    </option>
                                @endif
                            </select>
                        </label>

                        <label>
                            Quantity choice
                            <select class="quantity-preset">
                                <option value="custom">Custom quantity</option>
                            </select>
                        </label>

                        <label>
                            Quantity
                            <input
                                type="number"
                                name="quantity"
                                class="quantity-input"
                                min="{{ $isGeneric || $unit === 'kg' ? '0.01' : '1' }}"
                                max="1000"
                                step="{{ !$isGeneric && $unit === 'sack' ? '1' : '0.01' }}"
                                value="{{ $item['quantity'] }}"
                                required
                            >
                        </label>

                        <button type="submit">Update</button>
                    </form>

                    <form class="controls" method="POST" action="{{ route('cart.remove', $product) }}">
                        @csrf
                        @method('DELETE')
                        <button class="remove" type="submit">Remove item</button>
                    </form>
                </div>

                <div class="price">
                    @if ($item['confirmed'])
                        <div>
                            &#8369;{{ number_format($item['price'], 2) }}
                            <span class="muted">/ {{ $unitLabel($unit) }}</span>
                        </div>
                        <div class="muted">
                            Quantity: {{ number_format((float) $item['quantity'], 2) }}
                            {{ $unitLabel($unit) }}
                        </div>

                        @if (!$isGeneric && $unit === 'sack' && $product->sack_weight_kg)
                            <div class="muted">{{ $product->sack_weight_kg }} kg per sack</div>
                        @endif

                        <div class="muted">Subtotal: &#8369;{{ number_format($item['subtotal'], 2) }}</div>
                        <div class="muted">
                            {{ number_format($item['price'], 2) }} ×
                            {{ number_format((float) $item['quantity'], 2) }}
                            {{ $unitLabel($unit) }}
                        </div>
                    @else
                        <div class="notice error">Price to be confirmed</div>
                    @endif
                </div>
            </section>
        @endforeach

        <section class="summary">
            <h2>Order summary</h2>
            <p class="muted">Subtotal for confirmed prices</p>
            <div class="total">&#8369;{{ number_format($total, 2) }}</div>

            @if ($needsPriceConfirmation)
                <div class="notice error">
                    One or more products need price confirmation. This is not a final order total.
                </div>
            @endif

            @if ($hasUnavailableStock)
                <div class="notice error">
                    One or more products do not meet the minimum order or stock requirements. Update or remove those items before proceeding.
                </div>
            @endif

            <p class="muted">
                Delivery fees are not included. Your cart does not reserve inventory or place an order.
            </p>

            <div class="actions">
                <button class="button secondary" type="button" data-open-product-picker>Add product</button>
                <a class="button secondary" href="{{ route('home') }}" target="_top">Continue shopping</a>
                @if ($canProceed)
                    <a class="button" href="{{ route('checkout.create') }}">Proceed to checkout</a>
                @else
                    <span class="button disabled" aria-disabled="true">Proceed to checkout</span>
                @endif
            </div>
        </section>
    @else
        <section class="empty">
            <h2>Your cart is empty</h2>
            <p class="muted">Choose products to get started.</p>
            <button class="button" type="button" data-open-product-picker>Add product</button>
        </section>
    @endif

    <dialog class="product-picker" id="product-picker" aria-labelledby="product-picker-heading">
        <div class="product-picker-header">
            <div>
                <h2 id="product-picker-heading">Add a product</h2>
                <p class="muted">Choose an available product to add it to your current cart.</p>
            </div>
            <button class="product-picker-close" type="button" data-close-product-picker aria-label="Close product list">&times;</button>
        </div>

        @if ($availableProducts->isNotEmpty())
            <label class="product-picker-search">
                Search products
                <input type="search" id="product-picker-search" placeholder="Search by product name or brand">
            </label>

            <div class="product-picker-grid" id="product-picker-grid">
                @foreach ($availableProducts as $availableItem)
                    @php
                        $availableProduct = $availableItem['product'];
                    @endphp
                    <article
                        class="product-picker-card"
                        data-product-search="{{ strtolower($availableProduct->name . ' ' . ($availableProduct->brand ?? '') . ' ' . ($availableProduct->category ?? '')) }}"
                    >
                        @if ($availableProduct->image)
                            <img src="{{ asset('storage/' . $availableProduct->image) }}" alt="" loading="lazy">
                        @else
                            <span class="add-on-image-placeholder" aria-hidden="true">Item</span>
                        @endif

                        <div class="add-on-details">
                            <h3>{{ $availableProduct->name }}</h3>
                            @if ($availableProduct->brand)
                                <p class="muted">{{ $availableProduct->brand }}</p>
                            @endif
                            <p class="muted">{{ $availableItem['stock_text'] }}</p>
                            <p class="muted">&#8369;{{ number_format($availableItem['price'], 2) }} / {{ $availableItem['unit'] }}</p>
                        </div>

                        <form method="POST" action="{{ route('cart.add', $availableProduct) }}">
                            @csrf
                            <input type="hidden" name="unit" value="{{ $availableItem['unit'] }}">
                            <input type="hidden" name="quantity" value="{{ $availableItem['quantity'] }}">
                            <button type="submit">Add {{ $availableProduct->name }}</button>
                        </form>
                    </article>
                @endforeach
                <p class="muted product-picker-empty" id="product-picker-no-results" hidden>No matching products found.</p>
            </div>
        @else
            <p class="muted">There are no other available products to add right now.</p>
        @endif
    </dialog>

    @if ($suggestedItems->isNotEmpty())
        <section class="add-ons" aria-labelledby="add-ons-heading">
            <h2 id="add-ons-heading">Add something to go with it</h2>
            <p class="muted">A few available items you can add to this same order.</p>

            <div class="add-on-grid">
                @foreach ($suggestedItems as $suggestion)
                    @php
                        $suggestedProduct = $suggestion['product'];
                    @endphp
                    <article class="add-on-item">
                        @if ($suggestedProduct->image)
                            <img src="{{ asset('storage/' . $suggestedProduct->image) }}" alt="" loading="lazy">
                        @else
                            <span class="add-on-image-placeholder" aria-hidden="true">Item</span>
                        @endif

                        <div class="add-on-details">
                            <h3>{{ $suggestedProduct->name }}</h3>
                            <p class="muted">{{ $suggestion['stock_text'] }}</p>
                            <p class="muted">&#8369;{{ number_format($suggestion['price'], 2) }} / {{ $suggestion['unit'] }}</p>
                        </div>

                        <form method="POST" action="{{ route('cart.add', $suggestedProduct) }}">
                            @csrf
                            <input type="hidden" name="unit" value="{{ $suggestion['unit'] }}">
                            <input type="hidden" name="quantity" value="{{ $suggestion['quantity'] }}">
                            <button type="submit">Add {{ $suggestedProduct->name }}</button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</main>

<footer>H&K Consumer Goods · H+K Consumer Goods Store</footer>

<script>
const productPicker = document.getElementById('product-picker');
document.querySelectorAll('[data-open-product-picker]').forEach(button => {
    button.addEventListener('click', () => productPicker?.showModal());
});
document.querySelector('[data-close-product-picker]')?.addEventListener('click', () => productPicker?.close());
productPicker?.addEventListener('click', event => {
    if (event.target === productPicker) productPicker.close();
});

const productSearch = document.getElementById('product-picker-search');
productSearch?.addEventListener('input', () => {
    const query = productSearch.value.trim().toLowerCase();
    const cards = Array.from(document.querySelectorAll('.product-picker-card'));
    let visibleCount = 0;
    cards.forEach(card => {
        const matches = card.dataset.productSearch.includes(query);
        card.hidden = !matches;
        if (matches) visibleCount++;
    });
    const noResults = document.getElementById('product-picker-no-results');
    if (noResults) noResults.hidden = visibleCount > 0;
});

document.querySelectorAll('.cart-update-form').forEach(form => {
    const unitSelect = form.querySelector('.unit-select');
    const quantityInput = form.querySelector('.quantity-input');
    const quantityPreset = form.querySelector('.quantity-preset');
    const isGeneric = unitSelect.dataset.generic === '1';

    function updateRules(reset = false) {
        const unit = unitSelect.value;
        const isSack = !isGeneric && unit === 'sack';
        const minimum = isSack ? 1 : 0.01;
        quantityInput.min = String(minimum);
        quantityInput.step = isSack ? '1' : '0.01';

        const unitName = isGeneric
            ? unitSelect.options[unitSelect.selectedIndex].text
            : (isSack ? 'sack' : 'kg');
        quantityPreset.replaceChildren();
        if (!isSack) {
            quantityPreset.add(new Option('Half ' + unitName, '0.5'));
        }
        quantityPreset.add(new Option('1 ' + unitName, '1'));
        quantityPreset.add(new Option('2 x ' + unitName, '2'));
        quantityPreset.add(new Option('Custom quantity', 'custom'));

        let value = Number(quantityInput.value);
        if (reset || !Number.isFinite(value) || value < minimum) {
            value = 1;
        }
        quantityInput.value = String(value);
        quantityPreset.value = quantityPreset.querySelector('option[value="' + value + '"]')
            ? String(value)
            : 'custom';
    }

    unitSelect.addEventListener('change', () => updateRules(true));
    quantityPreset.addEventListener('change', () => {
        if (quantityPreset.value !== 'custom') {
            quantityInput.value = quantityPreset.value;
        }
    });

    quantityInput.addEventListener('change', () => {
        const minimum = Number(quantityInput.min);
        const maximum = Number(quantityInput.max) || 1000;
        let value = Number(quantityInput.value);
        if (!Number.isFinite(value)) value = minimum;
        value = Math.min(maximum, Math.max(minimum, value));
        if (!(!isGeneric && unitSelect.value === 'sack')) {
            value = Math.round(value * 100) / 100;
        } else {
            value = Math.floor(value);
        }
        quantityInput.value = String(value);
        quantityPreset.value = quantityPreset.querySelector('option[value="' + value + '"]')
            ? String(value)
            : 'custom';
    });
});
</script>
@include('components.smart-assistant')
</body>
</html>
