<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bundle Packs | H&amp;K Consumer Goods Store</title>
    @vite('resources/js/app.js')
    <style>
        :root { --green: #245b38; --border: #dfe6dc; --muted: #657267; --text: #263b2b; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f8f3; color: var(--text); font: 14px/1.5 Arial, Helvetica, sans-serif; }
        header { padding: 16px 4%; background: var(--green); color: #fff; }
        main { width: min(1050px, calc(100% - 32px)); margin: 28px auto; }
        .heading { display: flex; align-items: end; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin-bottom: 20px; }
        h1 { margin: 0; font-size: 24px; }
        .muted { color: var(--muted); }
        .bundle-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 290px), 1fr)); gap: 14px; }
        .bundle { padding: 18px; border: 1px solid var(--border); border-radius: 8px; background: #fff; }
        .bundle h2 { margin: 0 0 5px; font-size: 18px; }
        .bundle ul { padding-left: 20px; }
        button, .button { min-height: 42px; padding: 10px 15px; border: 0; border-radius: 6px; background: var(--green); color: #fff; font: inherit; font-weight: 700; text-decoration: none; cursor: pointer; }
        .empty { padding: 28px; border: 1px dashed #b9c9b8; border-radius: 8px; background: #fff; text-align: center; }
    </style>
</head>
<body>
<header>H&amp;K Consumer Goods Store</header>
<main>
    <div class="heading">
        <div>
            <h1>Bundle Packs</h1>
            <p class="muted">Curated products to add together in one order. Prices follow each product's confirmed store price.</p>
        </div>
        <a class="button" href="{{ route('home') }}">Browse products</a>
    </div>

    @if (session('cart_error'))
        <p role="alert">{{ session('cart_error') }}</p>
    @endif

    @if ($bundles->isEmpty())
        <section class="empty"><h2>No bundle packs available</h2><p class="muted">Check back later for curated packs.</p></section>
    @else
        <div class="bundle-grid">
            @foreach ($bundles as $bundle)
                <article class="bundle">
                    <h2>{{ $bundle->name }}</h2>
                    @if ($bundle->description)<p class="muted">{{ $bundle->description }}</p>@endif
                    <ul>
                        @foreach ($bundle->items as $item)
                            <li>{{ $item->product?->name ?? 'Unavailable product' }} × {{ number_format((float) $item->quantity, 2) }} {{ $item->unit }}</li>
                        @endforeach
                    </ul>
                    @auth
                        @if (auth()->user()->isCustomer())
                            <form method="POST" action="{{ route('bundles.add-to-cart', $bundle) }}">
                                @csrf
                                <button type="submit">Add bundle to cart</button>
                            </form>
                        @endif
                    @else
                        <a class="button" href="{{ route('login') }}">Log in to order</a>
                    @endauth
                </article>
            @endforeach
        </div>
    @endif
</main>
@include('components.smart-assistant')
</body>
</html>