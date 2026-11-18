@extends('admin.layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
<style>
    .purchase-order-form > .field,
    .purchase-order-form .purchase-order-item .field { margin-bottom: 14px; }
    .notice.warning {
        padding: 13px 15px;
        margin-bottom: 18px;
        border: 1px solid #ead9ae;
        border-radius: 7px;
        background: #fff8e8;
        color: #725514;
    }
    :root[data-theme="dark"] .notice.warning {
        border-color: #67552a;
        background: #2a2518;
        color: #f0d99b;
    }
</style>
<div class="page-heading">
    <h1>Create Purchase Order</h1>
    <a class="button" href="{{ route('admin.purchase-orders.index') }}">Back to Purchase Orders</a>
</div>

@if ($errors->any())
    <div class="notice error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@if ($suppliers->isEmpty() || $products->isEmpty())
    <div class="notice warning">An active supplier and at least one active product are required before creating a purchase order.</div>
@endif

<form method="POST" action="{{ route('admin.purchase-orders.store') }}" class="panel purchase-order-form">
    @csrf
    <div class="field">
        <label for="supplier_id">Supplier</label>
        <select id="supplier_id" name="supplier_id" required>
            <option value="">Select a supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="expected_at">Expected Delivery Date</label>
        <input id="expected_at" type="date" name="expected_at" value="{{ old('expected_at') }}">
    </div>
    <div class="field">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" maxlength="2000">{{ old('notes') }}</textarea>
    </div>

    <h2>Items to Order</h2>
    <div id="purchase-order-items">
        @foreach (old('items', [[]]) as $index => $oldItem)
            <div class="purchase-order-item" style="display:grid;grid-template-columns: minmax(220px,2fr) 1fr 1fr auto;gap:12px;align-items:end;margin-bottom:12px">
                <div class="field">
                    <label>Product</label>
                    <select name="items[{{ $index }}][product_id]" required>
                        <option value="">Select product</option>
                        @foreach ($products as $product)
                            @php($unit = ($product->inventory_type ?? 'rice') === 'generic' ? ($product->base_unit ?: $product->stock_unit ?: 'unit') : 'kg')
                            <option value="{{ $product->id }}" @selected(($oldItem['product_id'] ?? '') == $product->id)>{{ $product->name }} ({{ $unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Quantity</label>
                    <input type="number" name="items[{{ $index }}][ordered_quantity]" min="0.001" max="1000000" step="0.001" value="{{ $oldItem['ordered_quantity'] ?? '' }}" required>
                </div>
                <div class="field">
                    <label>Unit Cost (optional)</label>
                    <input type="number" name="items[{{ $index }}][unit_cost]" min="0" max="1000000" step="0.01" value="{{ $oldItem['unit_cost'] ?? '' }}">
                </div>
                <button class="button button-danger remove-order-item" type="button">Remove</button>
            </div>
        @endforeach
    </div>
    <button class="button" id="add-order-item" type="button">Add Item</button>
    <button class="button" type="submit" @disabled($suppliers->isEmpty() || $products->isEmpty())>Create Purchase Order</button>
</form>

<template id="purchase-order-item-template">
    <div class="purchase-order-item" style="display:grid;grid-template-columns:minmax(220px,2fr) 1fr 1fr auto;gap:12px;align-items:end;margin-bottom:12px">
        <div class="field">
            <label>Product</label>
            <select data-field="product_id" required>
                <option value="">Select product</option>
                @foreach ($products as $product)
                    @php($unit = ($product->inventory_type ?? 'rice') === 'generic' ? ($product->base_unit ?: $product->stock_unit ?: 'unit') : 'kg')
                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $unit }})</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>Quantity</label>
            <input data-field="ordered_quantity" type="number" min="0.001" max="1000000" step="0.001" required>
        </div>
        <div class="field">
            <label>Unit Cost (optional)</label>
            <input data-field="unit_cost" type="number" min="0" max="1000000" step="0.01">
        </div>
        <button class="button button-danger remove-order-item" type="button">Remove</button>
    </div>
</template>
<script>
(() => {
    const container = document.getElementById('purchase-order-items');
    let nextIndex = 0;
    const reindexRows = () => {
        container.querySelectorAll('.purchase-order-item').forEach((row, index) => {
            row.querySelectorAll('[name^="items["]').forEach((input) => {
                const match = input.name.match(/^items\[\d+\]\[([^\]]+)\]$/);
                if (match) input.name = `items[${index}][${match[1]}]`;
            });
        });
        nextIndex = container.children.length;
    };

    reindexRows();

    document.getElementById('add-order-item').addEventListener('click', () => {
        if (container.children.length >= 50) return;
        const row = document.getElementById('purchase-order-item-template').content.firstElementChild.cloneNode(true);
        row.querySelectorAll('[data-field]').forEach((input) => {
            input.name = `items[${nextIndex}][${input.dataset.field}]`;
        });
        nextIndex += 1;
        container.appendChild(row);
    });

    container.addEventListener('click', (event) => {
        if (event.target.closest('.remove-order-item') && container.children.length > 1) {
            event.target.closest('.purchase-order-item').remove();
            reindexRows();
        }
    });
})();
</script>
@endsection
