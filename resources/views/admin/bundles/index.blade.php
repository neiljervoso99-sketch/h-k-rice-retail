@extends('admin.layouts.app')

@section('title', 'Bundle Packs')

@section('content')
    <div class="page-heading">
        <h1>Bundle Packs</h1>
        <p>Group products customers can add to one cart together.</p>
    </div>

    <section class="panel" style="margin-bottom:20px;">
        <h2>Create a Bundle</h2>
        <form method="POST" action="{{ route('admin.bundles.store') }}">
            @csrf
            <div class="fields">
                <div class="field">
                    <label for="bundle-name">Bundle name</label>
                    <input id="bundle-name" name="name" maxlength="150" required>
                </div>
                <div class="field">
                    <label for="bundle-description">Description</label>
                    <input id="bundle-description" name="description" maxlength="1000">
                </div>
            </div>

            @for ($slot = 0; $slot < 2; $slot++)
                <fieldset style="margin:16px 0; padding:14px; border:1px solid var(--border); border-radius:8px;">
                    <legend>Bundle item {{ $slot + 1 }}</legend>
                    <div class="fields">
                        <div class="field">
                            <label for="bundle-product-{{ $slot }}">Product</label>
                            <select id="bundle-product-{{ $slot }}" name="items[{{ $slot }}][product_id]" required>
                                <option value="">Select product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="bundle-unit-{{ $slot }}">Unit</label>
                            <input id="bundle-unit-{{ $slot }}" name="items[{{ $slot }}][unit]" value="kg" maxlength="30" required>
                        </div>
                        <div class="field">
                            <label for="bundle-quantity-{{ $slot }}">Quantity</label>
                            <input id="bundle-quantity-{{ $slot }}" name="items[{{ $slot }}][quantity]" type="number" min="0.01" max="1000" step="0.01" value="5" required>
                        </div>
                    </div>
                </fieldset>
            @endfor

            @error('items')
                <p role="alert" style="color:#a52b24;">{{ $message }}</p>
            @enderror
            <button class="button" type="submit">Create Bundle</button>
        </form>
    </section>

    <section class="panel" style="overflow-x:auto;">
        <h2>Existing Bundles</h2>
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr><th style="padding:10px;">Bundle</th><th style="padding:10px;">Items</th><th style="padding:10px;">Status</th></tr>
            </thead>
            <tbody>
                @forelse ($bundles as $bundle)
                    <tr>
                        <td style="padding:10px;"><strong>{{ $bundle->name }}</strong><br><span class="muted">{{ $bundle->description }}</span></td>
                        <td style="padding:10px;">{{ $bundle->items->map(fn ($item) => $item->product?->name . ' × ' . $item->quantity . ' ' . $item->unit)->implode(', ') }}</td>
                        <td style="padding:10px;">{{ $bundle->is_active ? 'Active' : 'Inactive' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="padding:10px;">No bundle packs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $bundles->links() }}
    </section>
@endsection