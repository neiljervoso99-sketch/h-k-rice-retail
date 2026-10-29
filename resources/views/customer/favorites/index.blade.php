<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Favorites | H&amp;K Consumer Goods Store</title>
    @vite('resources/js/app.js')
    <style>
        :root { --green: #245b38; --text: #263b2b; --muted: #657267; --border: #dfe6dc; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f8f3; color: var(--text); font: 14px/1.5 Arial, Helvetica, sans-serif; }
        header { padding: 16px 4%; background: var(--green); color: #fff; }
        main { width: min(1050px, calc(100% - 32px)); margin: 28px auto; }
        .topline { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        h1 { margin: 0; font-size: 24px; }
        .muted { color: var(--muted); }
        .favorite-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 270px), 1fr)); gap: 14px; }
        .favorite-card { display: grid; grid-template-columns: 76px minmax(0, 1fr); gap: 13px; padding: 15px; border: 1px solid var(--border); border-radius: 8px; background: #fff; }
        .favorite-card img, .placeholder { display: grid; width: 76px; height: 76px; place-items: center; border-radius: 6px; background: #edf3eb; object-fit: cover; color: var(--muted); }
        .favorite-copy { min-width: 0; }
        .favorite-copy h2 { margin: 0; font-size: 16px; overflow-wrap: anywhere; }
        .favorite-copy p { margin: 4px 0; color: var(--muted); }
        .favorite-actions { grid-column: 1 / -1; display: flex; gap: 8px; flex-wrap: wrap; }
        button, .button { min-height: 40px; padding: 9px 13px; border: 1px solid var(--green); border-radius: 6px; background: var(--green); color: #fff; font: inherit; font-weight: 700; text-decoration: none; cursor: pointer; }
        button.remove { border-color: var(--border); background: #fff; color: #8d2929; }
        .empty { padding: 28px; border: 1px dashed #b9c9b8; border-radius: 8px; background: #fff; text-align: center; }
        @media (max-width: 480px) { main { margin-top: 20px; } .favorite-card { grid-template-columns: 60px minmax(0, 1fr); padding: 12px; } .favorite-card img, .placeholder { width: 60px; height: 60px; } .favorite-actions > * { flex: 1 1 100%; } }
    </style>
</head>
<body>
<header>H&amp;K Consumer Goods Store</header>
<main>
    @if (session('success'))
        <p role="status">{{ session('success') }}</p>
    @endif
    <div class="topline">
        <div>
            <h1>My Favorites</h1>
            <p class="muted">Products you saved for your next order.</p>
        </div>
        <a class="button" href="{{ route('home') }}">Browse products</a>
    </div>

    @if ($products->isEmpty())
        <section class="empty">
            <h2>No saved products yet</h2>
            <p class="muted">Save products from the store to find them here later.</p>
            <a class="button" href="{{ route('home') }}">Browse products</a>
        </section>
    @else
        <div class="favorite-grid">
            @foreach ($products as $product)
                @php
                    $generic = ($product->inventory_type ?? 'rice') === 'generic';
                    $unit = $generic
                        ? $product->sellingUnits->first(fn ($sellingUnit) => $sellingUnit->is_active && (float) $sellingUnit->selling_price > 0)
                        : null;
                    $riceUnit = (float) $product->price_per_kg > 0 && (float) $product->stock_kg >= 5
                        ? 'kg'
                        : (($product->price_per_sack && $product->sack_weight_kg && (float) $product->stock_kg >= (float) $product->sack_weight_kg) ? 'sack' : null);
                    $canAdd = $product->price_confirmed && ($generic
                        ? $unit && (float) $product->stock_base_quantity >= (float) $unit->conversion_factor
                        : $riceUnit !== null);
                    $unitName = $generic ? ($unit?->unit_name ?? $product->base_unit ?? $product->stock_unit ?? 'unit') : $riceUnit;
                    $unitPrice = $generic ? (float) ($unit?->selling_price ?? 0) : (float) ($riceUnit === 'sack' ? $product->price_per_sack : $product->price_per_kg);
                @endphp
                <article class="favorite-card">
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="" loading="lazy">
                    @else
                        <span class="placeholder" aria-hidden="true">Product</span>
                    @endif
                    <div class="favorite-copy">
                        <h2>{{ $product->name }}</h2>
                        <p>{{ $product->category ?: $product->rice_type ?: 'Store product' }}</p>
                        <p>
                            @if ($canAdd)
                                &#8369;{{ number_format($unitPrice, 2) }} / {{ $unitName }}
                            @else
                                Price or stock unavailable
                            @endif
                        </p>
                    </div>
                    <div class="favorite-actions">
                        @if ($canAdd)
                            <form method="POST" action="{{ route('cart.add', $product) }}">
                                @csrf
                                <input type="hidden" name="unit" value="{{ $unitName }}">
                                <input type="hidden" name="quantity" value="{{ $generic ? 1 : ($riceUnit === 'kg' ? 5 : 1) }}">
                                <button type="submit">Add to Cart</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('customer.favorites.destroy', $product) }}">
                            @csrf
                            @method('DELETE')
                            <button class="remove" type="submit">Remove</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</main>
@include('components.smart-assistant')
</body>
</html>