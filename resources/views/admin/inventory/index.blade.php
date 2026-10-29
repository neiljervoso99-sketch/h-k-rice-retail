@extends('admin.layouts.app')

@section('title', 'Inventory')

@section('content')
<style>
    .inventory-dashboard {
        --inventory-green: #245333;
        --inventory-muted: #68786c;
        --inventory-border: #e0e8e1;
        max-width: 1680px;
        margin: 0 auto;
    }

    .inventory-dashboard .page-heading {
        padding: 22px 24px;
        margin-bottom: 18px;
        border: 1px solid var(--inventory-border);
        border-radius: 14px;
        background: linear-gradient(110deg, #fff 0%, #f2f8f2 100%);
    }

    .inventory-dashboard .page-heading h1 {
        color: #1d422a;
        font-size: 28px;
    }

    .inventory-dashboard .inventory-overview {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .inventory-dashboard .inventory-stat {
        position: relative;
        overflow: hidden;
        padding: 17px 18px;
        border: 1px solid var(--inventory-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(27, 55, 34, .045);
    }

    .inventory-dashboard .inventory-stat::after {
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #4a8756;
        content: "";
    }

    .inventory-dashboard .inventory-stat.warning::after {
        background: #d99a2b;
    }

    .inventory-dashboard .inventory-stat.danger::after {
        background: #c94b41;
    }

    .inventory-dashboard .inventory-stat-label {
        display: block;
        color: var(--inventory-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .inventory-dashboard .inventory-stat-value {
        display: block;
        margin-top: 6px;
        color: #213e29;
        font-size: 25px;
        font-weight: 800;
        line-height: 1.15;
    }

    .inventory-dashboard .inventory-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        align-items: start;
    }

    .inventory-dashboard .panel {
        min-width: 0;
        padding: 20px;
        margin-bottom: 16px;
        border: 1px solid var(--inventory-border);
        border-radius: 13px;
        box-shadow: 0 5px 18px rgba(27, 55, 34, .045);
    }

    .inventory-dashboard .panel > h2 {
        margin-bottom: 6px;
        color: #234a30;
        font-size: 18px;
    }

    .inventory-dashboard .panel > p {
        margin: 0 0 16px;
        color: var(--inventory-muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .inventory-dashboard .inventory-actions-grid .panel {
        height: 100%;
    }

    .inventory-dashboard .fields {
        gap: 13px;
    }

    .inventory-dashboard .field label,
    .inventory-dashboard .panel label {
        color: #354c3b;
        font-size: 12px;
    }

    .inventory-dashboard .field input,
    .inventory-dashboard .field select,
    .inventory-dashboard .field textarea {
        min-height: 42px;
        border-color: #d3ded4;
        border-radius: 8px;
        font-size: 13px;
    }

    .inventory-dashboard .field textarea {
        min-height: 74px;
    }

    .inventory-dashboard #stock_in_form > .field {
        margin-bottom: 14px;
    }

    .inventory-dashboard #stock_in_form > .field:nth-of-type(2) {
        padding: 13px;
        border: 1px dashed #d6e2d7;
        border-radius: 10px;
        background: #f9fbf9;
    }

    .inventory-dashboard #lookup_qr_button,
    .inventory-dashboard #start_qr_camera {
        width: auto;
        min-height: 36px;
        margin: 8px 6px 0 0;
        padding: 8px 12px;
        border-radius: 7px;
        font-size: 12px;
    }

    .inventory-dashboard #matched_product_panel {
        padding: 14px;
        border-color: #d6e5d8;
        border-radius: 10px;
        background: #f4faf4;
    }

    .inventory-dashboard #matched_product_panel h3 {
        margin-bottom: 10px;
        color: #245333;
        font-size: 15px;
    }

    .inventory-dashboard #matched_product_panel p {
        margin: 5px 0;
        font-size: 12px;
    }

    .inventory-dashboard .panel .button[type="submit"],
    .inventory-dashboard .panel > form > .button {
        min-height: 42px;
        margin-top: 14px;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 13px;
    }

    .inventory-dashboard .notice.warning {
        padding: 12px 14px;
        margin: 10px 0;
        border: 1px solid #f0dfb8;
        border-left: 4px solid #d99a2b;
        border-radius: 9px;
        background: #fffaf0;
        color: #5a492b;
        font-size: 13px;
    }

    .inventory-dashboard .table-wrap {
        border: 1px solid #e6ece6;
        border-radius: 10px;
        background: #fff;
    }

    .inventory-dashboard .forecast-status {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 5px;
        background: #eaf3e9;
        color: #285337;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .inventory-dashboard .forecast-status.warning {
        background: #fff3d8;
        color: #765514;
    }

    .inventory-dashboard .forecast-status.danger {
        background: #ffebe8;
        color: #9c3028;
    }

    .inventory-dashboard table {
        min-width: 700px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .inventory-dashboard th {
        padding: 12px 13px;
        background: #f2f6f1;
        color: #35523c;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .inventory-dashboard td {
        padding: 12px 13px;
        border-bottom-color: #edf1ed;
        color: #35443a;
        font-size: 12px;
        vertical-align: middle;
    }

    .inventory-dashboard tbody tr:last-child td {
        border-bottom: 0;
    }

    .inventory-dashboard tbody tr:hover {
        background: #f9fbf9;
    }

    .inventory-dashboard .inventory-product {
        display: flex;
        min-width: 170px;
        align-items: center;
        gap: 10px;
    }

    .inventory-dashboard .inventory-product img,
    .inventory-dashboard .inventory-product-placeholder {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border: 1px solid #e4eae4;
        border-radius: 8px;
        background: #f4f7f3;
        object-fit: cover;
    }

    .inventory-dashboard .inventory-product-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #78877b;
        font-size: 10px;
    }

    .inventory-dashboard .inventory-status {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 999px;
        background: #eaf5ec;
        color: #26613a;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .inventory-dashboard .inventory-status.low {
        background: #fff3d8;
        color: #80540d;
    }

    .inventory-dashboard .inventory-status.out,
    .inventory-dashboard .inventory-status.inactive {
        background: #fde9e7;
        color: #a1322a;
    }

    .inventory-dashboard .movement-filters {
        margin-bottom: 16px;
        padding: 14px;
        border: 1px solid #e6ece6;
        border-radius: 10px;
        background: #f9fbf9;
    }

    .inventory-dashboard .movement-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .inventory-dashboard .movement-filter-actions .button {
        min-height: 38px;
        padding: 9px 13px;
        border-radius: 7px;
        font-size: 12px;
    }

    :root[data-theme="dark"] .inventory-dashboard {
        --inventory-muted: #aab6cb;
        --inventory-border: #293149;
    }

    :root[data-theme="dark"] .inventory-dashboard .page-heading {
        border-color: var(--inventory-border);
        background: #131b2e;
    }

    :root[data-theme="dark"] .inventory-dashboard .page-heading h1,
    :root[data-theme="dark"] .inventory-dashboard .inventory-stat-value,
    :root[data-theme="dark"] .inventory-dashboard .field label,
    :root[data-theme="dark"] .inventory-dashboard .panel label {
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .inventory-dashboard .page-heading p,
    :root[data-theme="dark"] .inventory-dashboard .panel > p,
    :root[data-theme="dark"] .inventory-dashboard .inventory-stat-label {
        color: var(--inventory-muted);
    }

    :root[data-theme="dark"] .inventory-dashboard .inventory-stat {
        border-color: var(--inventory-border);
        background: #131b2e;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .14);
    }

    :root[data-theme="dark"] .inventory-dashboard .field input,
    :root[data-theme="dark"] .inventory-dashboard .field select,
    :root[data-theme="dark"] .inventory-dashboard .field textarea {
        border-color: #3a465e;
    }

    :root[data-theme="dark"] .inventory-dashboard #stock_in_form > .field:nth-of-type(2),
    :root[data-theme="dark"] .inventory-dashboard .movement-filters {
        border-color: var(--inventory-border);
        background: #11192a;
    }

    :root[data-theme="dark"] .inventory-dashboard #matched_product_panel {
        border-color: #35533e;
        background: #172a22;
    }

    :root[data-theme="dark"] .inventory-dashboard #matched_product_panel h3 {
        color: #c9ead0;
    }

    :root[data-theme="dark"] .inventory-dashboard .notice.warning {
        border-color: #66512a;
        background: #332a18;
        color: #f5d894;
    }

    :root[data-theme="dark"] .inventory-dashboard .table-wrap {
        border-color: var(--inventory-border);
        background: #11192a;
    }

    :root[data-theme="dark"] .inventory-dashboard th {
        border-bottom-color: var(--inventory-border);
    }

    :root[data-theme="dark"] .inventory-dashboard td {
        border-bottom-color: var(--inventory-border);
        color: #d3dbed;
    }

    :root[data-theme="dark"] .inventory-dashboard tbody tr:hover {
        background: #1a2439;
    }

    :root[data-theme="dark"] .inventory-dashboard .inventory-product-placeholder {
        border-color: var(--inventory-border);
        background: #1a2439;
        color: #aab6cb;
    }

    @media (max-width: 1100px) {
        .inventory-dashboard .inventory-actions-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .inventory-dashboard .inventory-actions-grid .panel {
            height: auto;
        }

        .inventory-dashboard .inventory-overview {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .inventory-dashboard .page-heading {
            padding: 17px;
        }

        .inventory-dashboard .page-heading h1 {
            font-size: 23px;
        }

        .inventory-dashboard .inventory-stat {
            padding: 14px;
        }

        .inventory-dashboard .inventory-stat-value {
            font-size: 21px;
        }

        .inventory-dashboard .inventory-actions-grid .fields,
        .inventory-dashboard .movement-filters .fields {
            grid-template-columns: minmax(0, 1fr);
        }

        .inventory-dashboard .panel {
            padding: 15px;
        }
    }
</style>

<div class="inventory-dashboard">
<div class="page-heading">
    <h1>Inventory</h1>
    <p>Manage rice and general merchandise inventory using the appropriate unit for each product.</p>
</div>

@if (session('success'))
    <div class="notice success" role="status">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="notice error" role="alert">
        <strong>Please correct the following errors:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $unitLabels = [
        'piece' => 'pieces',
        'pack' => 'packs',
        'bottle' => 'bottles',
        'box' => 'boxes',
        'can' => 'cans',
        'bag' => 'bags',
        'kg' => 'kg',
        'gram' => 'grams',
        'liter' => 'liters',
        'ml' => 'ml',
        'roll' => 'rolls',
        'pair' => 'pairs',
        'dozen' => 'dozen',
    ];

    $activeInventoryCount = $products->where('is_active', true)->count();
    $outOfStockCount = $products->filter(function ($product) {
        if (!$product->is_active) {
            return false;
        }

        return (float) (
            ($product->inventory_type ?? 'rice') === 'generic'
                ? $product->stock_base_quantity
                : $product->stock_kg
        ) <= 0;
    })->count();
@endphp

<div class="inventory-overview">
    <div class="inventory-stat">
        <span class="inventory-stat-label">Active Products</span>
        <span class="inventory-stat-value">{{ number_format($activeInventoryCount) }}</span>
    </div>
    <div class="inventory-stat warning">
        <span class="inventory-stat-label">Low-Stock Alerts</span>
        <span class="inventory-stat-value">{{ number_format($lowStockProducts->count()) }}</span>
    </div>
    <div class="inventory-stat danger">
        <span class="inventory-stat-label">Out of Stock</span>
        <span class="inventory-stat-value">{{ number_format($outOfStockCount) }}</span>
    </div>
    <div class="inventory-stat">
        <span class="inventory-stat-label">Movements Shown</span>
        <span class="inventory-stat-value">{{ number_format($movements->count()) }}</span>
    </div>
</div>

<div class="inventory-actions-grid">
<div class="panel inventory-stock-in">
    <h2>Stock In</h2>
    <p>Select a product or scan its QR code, then enter the received quantity in its inventory unit.</p>

    <form method="POST" action="{{ route('admin.inventory.stock-in') }}" id="stock_in_form">
        @csrf

        <div class="field">
            <label for="stock_in_product_select">Select Product</label>
            <select id="stock_in_product_select">
                <option value="">Select a product</option>
                @foreach ($products as $product)
                    @if ($product->is_active)
                        <option
                            value="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-type="{{ $product->inventory_type ?? 'rice' }}"
                            data-unit="{{ $product->stock_unit ?? 'kg' }}"
                            data-stock-kg="{{ $product->stock_kg ?? 0 }}"
                            data-stock-quantity="{{ $product->stock_quantity ?? 0 }}"
                            data-base-unit="{{ $product->base_unit ?? $product->stock_unit ?? 'piece' }}"
                            data-base-stock="{{ $product->stock_base_quantity ?? 0 }}"
                            data-selling-units="{{ $product->sellingUnits->where('is_active', true)->values()->toJson() }}"
                            data-sack-weight="{{ $product->sack_weight_kg ?? 0 }}"
                            data-qr="{{ $product->qr_code ?? '' }}"
                        >
                            {{ $product->name }}
                            ({{ ($product->inventory_type ?? 'rice') === 'generic' ? ($product->base_unit ?? $product->stock_unit ?? 'piece') : 'rice' }})
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="qr_code_scan">Product QR Code (Optional)</label>
            <input id="qr_code_scan" type="text" placeholder="Scan or enter product QR code" autocomplete="off">
            <button class="button" type="button" id="lookup_qr_button">Find Product</button>
            <button class="button" type="button" id="start_qr_camera">Scan Using Camera</button>
            <div id="qr_reader" style="max-width:400px;margin-top:12px;"></div>
            <p id="qr_status" role="status" aria-live="polite"></p>
        </div>

        <input type="hidden" name="product_id" id="product_id" value="">
        <input type="hidden" name="inventory_type" id="inventory_type" value="">

        <div class="panel" id="matched_product_panel" hidden>
            <h3>Selected Product</h3>
            <p><strong>Name:</strong> <span id="matched_product_name"></span></p>
            <p><strong>Type:</strong> <span id="matched_product_type"></span></p>
            <p><strong>Current Stock:</strong> <span id="matched_product_stock"></span></p>
            <p id="matched_product_qr_row"><strong>QR Code:</strong> <span id="matched_product_qr"></span></p>
            <p id="matched_product_weight_row"><strong>Sack Weight:</strong> <span id="matched_product_weight"></span></p>
        </div>

        <div id="rice_stock_fields" hidden>
            <div class="fields">
                <div class="field">
                    <label for="sacks">Number of Sacks Received</label>
                    <input id="sacks" type="number" name="sacks" min="0" max="100000" step="1" value="{{ old('sacks', 0) }}">
                </div>

                <div class="field">
                    <label for="extra_kg">Additional Kilograms Received</label>
                    <input id="extra_kg" type="number" name="extra_kg" min="0" max="1000000" step="1" value="{{ old('extra_kg', 0) }}">
                </div>
            </div>
        </div>

        <div id="generic_stock_fields" hidden>
            <div class="fields">
                <div class="field">
                    <label for="generic_quantity">Quantity Received</label>
                    <input id="generic_quantity" type="number" name="quantity" min="0.01" max="1000000" step="0.01" value="{{ old('quantity') }}">
                </div>
                <div class="field">
                    <label for="generic_unit">Selling Unit</label>
                    <select id="generic_unit" name="unit" required>
                        <option value="">Select selling unit</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="stock_in_common_fields" hidden>
            <div class="fields">
                <div class="field">
                    <label for="movement_date">Stock-In Date</label>
                    <input id="movement_date" type="date" name="movement_date" value="{{ old('movement_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}">
                </div>

                <div class="field">
                    <label for="supplier">Supplier (Optional)</label>
                    <input id="supplier" type="text" name="supplier" maxlength="150" value="{{ old('supplier') }}" placeholder="Enter supplier name">
                </div>

                <div class="field">
                    <label for="batch_reference">Batch Reference (Optional)</label>
                    <input id="batch_reference" type="text" name="batch_reference" maxlength="150" value="{{ old('batch_reference') }}" placeholder="Delivery or invoice reference">
                </div>

                <div class="field">
                    <label for="delivery_reference">Unique Delivery Reference (Optional)</label>
                    <input id="delivery_reference" name="delivery_reference" type="text" maxlength="150" value="{{ old('delivery_reference') }}" placeholder="Unique delivery or receipt ID">
                    <small>Use a different reference for each delivery when available.</small>
                </div>

                <div class="field">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" maxlength="1500" rows="3" placeholder="Delivery condition or other details">{{ old('notes') }}</textarea>
                </div>
            </div>

            <button class="button btn btn-success" type="submit">Record Stock In</button>
        </div>
    </form>
</div>

<div class="panel inventory-adjustment">
    <h2>Stock Adjustment</h2>
    <p>Correct stock after a physical count, damage, wastage, or another inventory discrepancy.</p>

    <form method="POST" action="{{ route('admin.inventory.adjustment') }}">
        @csrf

        <div class="fields">
            <div class="field">
                <label for="adjustment_product_id">Product</label>
                <select id="adjustment_product_id" name="product_id" required>
                    <option value="">Select a product</option>
                    @foreach ($products as $product)
                        @if ($product->is_active)
                            <option
                                value="{{ $product->id }}"
                                data-type="{{ $product->inventory_type ?? 'rice' }}"
                                data-unit="{{ $product->inventory_type === 'generic' ? ($product->stock_unit ?? 'piece') : 'kg' }}"
                                data-stock-kg="{{ $product->stock_kg ?? 0 }}"
                                data-stock-quantity="{{ $product->stock_quantity ?? 0 }}"
                                data-base-unit="{{ $product->base_unit ?? $product->stock_unit ?? 'piece' }}"
                                data-base-stock="{{ $product->stock_base_quantity ?? 0 }}"
                                data-selling-units="{{ $product->sellingUnits->where('is_active', true)->values()->toJson() }}"
                                @selected(old('product_id') == $product->id)
                            >
                                {{ $product->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="adjustment_type">Adjustment Type</label>
                <select id="adjustment_type" name="adjustment_type" required>
                    <option value="adjustment_in" @selected(old('adjustment_type') === 'adjustment_in')>Add Stock</option>
                    <option value="adjustment_out" @selected(old('adjustment_type') === 'adjustment_out')>Remove Stock</option>
                </select>
            </div>

            <div class="field">
                <label for="adjustment_quantity">Quantity</label>
                <input
                    id="adjustment_quantity"
                    type="number"
                    name="quantity"
                    min="0.01"
                    max="1000000"
                    step="0.01"
                    value="{{ old('quantity') }}"
                    required
                >
            </div>

            <div class="field" id="adjustment_extra_kg_field" hidden>
                <label for="adjustment_extra_kg">Additional Quantity (kg)</label>
                <input
                    id="adjustment_extra_kg"
                    type="number"
                    name="extra_kg"
                    min="0"
                    max="1000000"
                    step="0.01"
                    value="{{ old('extra_kg', '0') }}"
                    disabled
                >
                <small>Use this field for additional kilograms when adjusting rice stock.</small>
            </div>

            <div class="field">
                <label for="adjustment_unit">Unit</label>
                <select id="adjustment_unit" name="unit" required data-initial-unit="{{ old('unit', 'kg') }}">
                    <option value="">Select unit</option>
                </select>
            </div>

            <div class="field">
                <label for="adjustment_reason">Reason</label>
                <select id="adjustment_reason" name="reason" required>
                    <option value="physical_count" @selected(old('reason') === 'physical_count')>Physical Count</option>
                    <option value="damage" @selected(old('reason') === 'damage')>Damage</option>
                    <option value="wastage" @selected(old('reason') === 'wastage')>Wastage</option>
                    <option value="other" @selected(old('reason') === 'other')>Other</option>
                </select>
            </div>

            <div class="field">
                <label for="adjustment_date">Adjustment Date</label>
                <input id="adjustment_date" type="date" name="movement_date" value="{{ old('movement_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
            </div>

            <div class="field">
                <label for="adjustment_notes">Notes (Optional)</label>
                <textarea id="adjustment_notes" name="notes" maxlength="1500" rows="3" placeholder="Explain the stock difference">{{ old('notes') }}</textarea>
            </div>
        </div>

        <button class="button btn btn-success" type="submit">Record Adjustment</button>
    </form>
</div>
</div>

@if ($lowStockProducts->isNotEmpty())
    <div class="panel inventory-low-stock">
        <h2>Low-Stock Alerts</h2>
        @foreach ($lowStockProducts as $product)
            @if (($product->inventory_type ?? 'rice') === 'generic')
                <div class="notice warning">
                    <strong>{{ $product->name }}</strong> —
                    {{ number_format((float) $product->stock_base_quantity, 2) }}
                    {{ $unitLabels[$product->base_unit ?: $product->stock_unit] ?? ($product->base_unit ?: $product->stock_unit) }}
                    remaining. Alert threshold:
                    {{ number_format((float) $product->low_stock_threshold_quantity, 2) }}
                    {{ $unitLabels[$product->base_unit ?: $product->stock_unit] ?? ($product->base_unit ?: $product->stock_unit) }}.
                </div>
            @else
                <div class="notice warning">
                    @php
                        $sackWeight = (float) ($product->sack_weight_kg ?? 0);
                        $stockKg = (float) ($product->stock_kg ?? 0);
                        $thresholdKg = (float) ($product->low_stock_threshold ?? 0);
                    @endphp

                    <strong>{{ $product->name }}</strong> —
                    @if ($sackWeight > 0)
                        {{ number_format(floor($stockKg / $sackWeight)) }} sacks
                        @if (round(fmod($stockKg, $sackWeight), 2) > 0)
                            + {{ number_format(fmod($stockKg, $sackWeight), 2) }} kg remaining
                        @endif
                        . Alert threshold:
                        {{ number_format($thresholdKg / $sackWeight, 2) }} sacks.
                    @else
                        {{ number_format($stockKg, 2) }} kg remaining.
                        Alert threshold: {{ number_format($thresholdKg, 2) }} kg.
                    @endif
                </div>
            @endif
        @endforeach
    </div>
@endif

<div class="panel inventory-stock-forecast">
    <h2>Sales-Based Stock Forecast</h2>
    <p>Estimated from recorded sales movements over the last 30 days. Use this as a restocking guide, not a guaranteed prediction.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Current Stock</th>
                    <th>Sold, 30 days</th>
                    <th>Daily Average</th>
                    <th>Estimated Days Left</th>
                    <th>Next 7 Days</th>
                    <th>Suggested Restock</th>
                    <th>Signal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stockForecasts as $forecast)
                    @php
                        $daysLeft = (float) $forecast['days_remaining'];
                        $signalClass = $daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : '');
                        $signalLabel = $daysLeft <= 7 ? 'Restock soon' : 'On track';
                    @endphp
                    <tr>
                        <td>{{ $forecast['product']->name }}</td>
                        <td>{{ number_format($forecast['stock'], 2) }} {{ $forecast['unit'] }}</td>
                        <td>{{ number_format($forecast['sold_quantity'], 2) }} {{ $forecast['unit'] }}</td>
                        <td>{{ number_format($forecast['daily_average'], 2) }} {{ $forecast['unit'] }}/day</td>
                        <td>{{ number_format($daysLeft, 1) }} days</td>
                        <td>{{ number_format($forecast['next_week_demand'], 2) }} {{ $forecast['unit'] }}</td>
                        <td>{{ number_format($forecast['restock_gap'], 2) }} {{ $forecast['unit'] }}</td>
                        <td><span class="forecast-status {{ $signalClass }}">{{ $signalLabel }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8">No recorded sales in the last 30 days to estimate product demand.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel inventory-current-stock">
    <h2>Current Stock</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Available Stock</th>
                    <th>Total Stock (kg)</th>
                    <th>Low-Stock Threshold</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    @php
                        $generic = ($product->inventory_type ?? 'rice') === 'generic';

                        $stock = $generic
                            ? (float) ($product->stock_base_quantity ?? 0)
                            : (float) ($product->stock_kg ?? 0);

                        $threshold = $generic
                            ? (float) ($product->low_stock_threshold_quantity ?? 0)
                            : (float) ($product->low_stock_threshold ?? 0);

                        $unit = $generic
                            ? ($unitLabels[$product->base_unit ?: $product->stock_unit] ?? ($product->base_unit ?: $product->stock_unit))
                            : 'kg';

                        $sackWeight = (float) ($product->sack_weight_kg ?? 0);

                        $fullSacks = (!$generic && $sackWeight > 0)
                            ? (int) floor($stock / $sackWeight)
                            : 0;

                        $remainingKg = (!$generic && $sackWeight > 0)
                            ? round($stock - ($fullSacks * $sackWeight), 2)
                            : $stock;

                        $thresholdSacks = (!$generic && $sackWeight > 0)
                            ? round($threshold / $sackWeight, 2)
                            : 0;
                    @endphp

                    <tr>
                        <td>
                            <div class="inventory-product">
                                @if ($product->image)
                                    <img
                                        src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/default-product.svg') }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';"
                                    >
                                @else
                                    <div class="inventory-product-placeholder">
                                        No image
                                    </div>
                                @endif

                                <span>{{ $product->name }}</span>
                            </div>
                        </td>
                        <td>{{ $generic ? 'General inventory' : 'Rice' }}</td>

                        <td>
                            @if ($generic)
                                {{ number_format($stock, 2) }} {{ $unit }}
                            @elseif ($sackWeight > 0)
                                {{ number_format($fullSacks) }} sacks
                            @else
                                {{ number_format($stock, 2) }} kg
                                <small>(Sack weight not configured)</small>
                            @endif
                        </td>

                        <td>
                            @if ($generic)
                                —
                            @else
                                {{ number_format($stock, 2) }} kg
                            @endif
                        </td>

                        <td>
                            @if ($generic)
                                {{ number_format($threshold, 2) }} {{ $unit }}
                            @elseif ($sackWeight > 0)
                                {{ number_format($thresholdSacks, 2) }} sacks
                            @else
                                {{ number_format($threshold, 2) }} kg
                            @endif
                        </td>

                        <td>
                            @if (!$product->is_active)
                                <span class="inventory-status inactive">Inactive</span>
                            @elseif ($stock <= 0)
                                <span class="inventory-status out">Out of Stock</span>
                            @elseif ($stock <= $threshold)
                                <span class="inventory-status low">Low Stock</span>
                            @else
                                <span class="inventory-status">In Stock</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel inventory-stock-in-history">
    <h2>Recent Stock-In History</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Quantity Added</th>
                    <th>Supplier / Batch Details</th>
                    <th>Delivery Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stockInMovements as $movement)
                    @php
                        $noteLines = preg_split('/\r\n|\r|\n/', $movement->notes ?? '');
                        $details = [];
                        foreach ($noteLines as $line) {
                            if (str_starts_with($line, 'Supplier: ') ||
                                str_starts_with($line, 'Batch Reference: ')) {
                                $details[] = $line;
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $movement->movement_date?->format('M d, Y') ?? '-' }}</td>
                        <td>{{ $movement->product_name ?? $movement->product?->name ?? '-' }}</td>
                        <td>
                            @if ($movement->quantity !== null)
                                {{ number_format((float) $movement->quantity, 2) }}
                                {{ $unitLabels[$movement->unit] ?? $movement->unit ?? 'units' }}
                            @else
                                {{ number_format((float) $movement->quantity_kg, 2) }} kg
                            @endif
                        </td>
                        <td>{{ count($details) ? implode(' | ', $details) : '-' }}</td>
                        <td>{{ $movement->delivery_reference ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No stock-in records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel inventory-movement-history">
    <h2>Inventory Movement History</h2>

    <form method="GET" action="{{ route('admin.inventory.index') }}" class="movement-filters">
        <div class="fields">
            <div class="field">
                <label for="date_from">Date From</label>
                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}" max="{{ now()->toDateString() }}">
            </div>
            <div class="field">
                <label for="date_to">Date To</label>
                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}" max="{{ now()->toDateString() }}">
            </div>
            <div class="field">
                <label for="filter_product_id">Product</label>
                <select id="filter_product_id" name="product_id">
                    <option value="">All Products</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? '') == $product->id)>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="movement_type">Movement Type</label>
                <select id="movement_type" name="movement_type">
                    <option value="all" @selected(($filters['movement_type'] ?? 'all') === 'all')>All Movements</option>
                    <option value="stock_in" @selected(($filters['movement_type'] ?? '') === 'stock_in')>Stock In</option>
                    <option value="sale" @selected(($filters['movement_type'] ?? '') === 'sale')>Sale / Stock Out</option>
                    <option value="adjustment_in" @selected(($filters['movement_type'] ?? '') === 'adjustment_in')>Adjustment - Add</option>
                    <option value="adjustment_out" @selected(($filters['movement_type'] ?? '') === 'adjustment_out')>Adjustment - Remove</option>
                </select>
            </div>
        </div>

        <div class="movement-filter-actions">
            <button class="button" type="submit">Apply Filters</button>
            <a href="{{ route('admin.inventory.index') }}" class="button">Clear Filters</a>
            <a href="{{ route('admin.inventory.export', request()->query()) }}" class="button">Export CSV</a>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Movement</th>
                    <th>Quantity</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td>{{ $movement->movement_date?->format('M d, Y') ?? '-' }}</td>
                        <td>{{ $movement->product_name ?? $movement->product?->name ?? '-' }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $movement->movement_type)) }}</td>
                        <td>
                            @if ($movement->quantity !== null)
                                {{ number_format((float) $movement->quantity, 2) }}
                                {{ $unitLabels[$movement->unit] ?? $movement->unit ?? 'units' }}
                            @else
                                {{ number_format((float) $movement->quantity_kg, 2) }} kg
                            @endif
                        </td>
                        <td>{{ $movement->notes ?: '-' }}</td>
                        <td>{{ $movement->creator?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No inventory movements recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const productSelect = document.getElementById('product_id');
    const stockInProductSelect = document.getElementById('stock_in_product_select');
    const inventoryType = document.getElementById('inventory_type');
    const riceFields = document.getElementById('rice_stock_fields');
    const genericFields = document.getElementById('generic_stock_fields');
    const commonFields = document.getElementById('stock_in_common_fields');
    const productPanel = document.getElementById('matched_product_panel');
    const status = document.getElementById('qr_status');
    const qrInput = document.getElementById('qr_code_scan');
    const lookupButton = document.getElementById('lookup_qr_button');
    const cameraButton = document.getElementById('start_qr_camera');

    const unitLabels = @json($unitLabels);
    let scanner = null;

    function toggleFields(container, enabled) {
        container.hidden = !enabled;
        container.querySelectorAll('input, select, textarea').forEach(input => {
            input.disabled = !enabled;
        });
    }

    function populateSellingUnits(select, units, selectedUnit = '') {
        select.replaceChildren(new Option('Select selling unit', ''));

        units.forEach(unit => {
            const name = String(unit.unit_name || '').trim();

            if (!name) return;

            const option = new Option(name, name.toLowerCase());
            option.selected = name.toLowerCase() === selectedUnit.toLowerCase();

            select.add(option);
        });
    }

    function updateProductDisplay(product) {
        const generic = product.inventory_type === 'generic';
        const unit = generic
            ? (product.base_unit || product.stock_unit || 'piece')
            : 'kg';
        const stock = generic
            ? Number(product.stock_base_quantity ?? product.stock_quantity ?? 0)
            : Number(product.stock_kg || 0);

        const sackWeight = Number(product.sack_weight_kg || 0);

        const genericUnitSelect = document.getElementById('generic_unit');
        populateSellingUnits(
            genericUnitSelect,
            product.selling_units || [],
            genericUnitSelect.value || unit
        );

        let stockText;

        if (generic) {
            stockText = stock.toLocaleString(undefined, {
                maximumFractionDigits: 2
            }) + ' ' + (unitLabels[unit] || unit);
        } else if (sackWeight > 0) {
            const fullSacks = Math.floor(stock / sackWeight);
            const remainingKg = Math.round((stock - fullSacks * sackWeight) * 100) / 100;

            stockText = fullSacks + ' sacks';

            if (remainingKg > 0) {
                stockText += ' + ' + remainingKg.toFixed(2) + ' kg remaining';
            }
        } else {
            stockText = stock.toFixed(2) + ' kg (sack weight not configured)';
        }

        inventoryType.value = generic ? 'generic' : 'rice';

        document.getElementById('matched_product_name').textContent = product.name || '';
        document.getElementById('matched_product_type').textContent =
            generic ? 'General inventory' : 'Rice';

        document.getElementById('matched_product_stock').textContent = stockText;
        document.getElementById('matched_product_qr').textContent = product.qr_code || 'Not assigned';
        document.getElementById('matched_product_weight').textContent =
            sackWeight > 0 ? sackWeight + ' kg/sack' : 'Not configured';

        document.getElementById('matched_product_qr_row').hidden = generic;
        document.getElementById('matched_product_weight_row').hidden = generic;

        productPanel.hidden = false;
        toggleFields(riceFields, !generic);
        toggleFields(genericFields, generic);
        toggleFields(commonFields, true);

    }

    function clearStockIn() {
        productPanel.hidden = true;
        toggleFields(riceFields, false);
        toggleFields(genericFields, false);
        toggleFields(commonFields, false);
        document.getElementById('generic_unit').value = '';
    }

    function selectProductFromDropdown() {
        const option = stockInProductSelect.selectedOptions[0];

        if (!option || !option.value) {
            productSelect.value = '';
            inventoryType.value = '';
            clearStockIn();
            status.textContent = '';
            return;
        }

        const generic = option.dataset.type === 'generic';

        const product = {
            id: option.value,
            name: option.dataset.name,
            inventory_type: option.dataset.type,
            stock_unit: option.dataset.unit,
            stock_quantity: option.dataset.stockQuantity,
            base_unit: option.dataset.baseUnit,
            stock_base_quantity: option.dataset.baseStock,
            selling_units: JSON.parse(option.dataset.sellingUnits || '[]'),
            stock_kg: option.dataset.stockKg,
            sack_weight_kg: option.dataset.sackWeight,
            qr_code: option.dataset.qr
        };

        productSelect.value = String(product.id);
        document.getElementById('generic_unit').value = '';
        updateProductDisplay(product);

        status.textContent = generic
            ? 'Product selected. Enter the received quantity and record Stock In.'
            : 'Rice product selected. Enter the received stock details.';
    }

    stockInProductSelect.addEventListener('change', selectProductFromDropdown);

    lookupButton.addEventListener('click', async () => {
        const code = qrInput.value.trim();

        clearStockIn();
        productSelect.value = '';
        inventoryType.value = '';
        stockInProductSelect.value = '';

        if (!code) {
            status.textContent = 'Please scan or enter a Product QR Code.';
            return;
        }

        lookupButton.disabled = true;
        status.textContent = 'Looking up product QR code...';

        try {
            const url = new URL(
                @json(route('admin.inventory.qr-lookup')),
                window.location.origin
            );

            url.searchParams.set('qr_code', code);

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (!response.ok || !data.id) {
                throw new Error('No product found for this QR code.');
            }

            productSelect.value = String(data.id);
            stockInProductSelect.value = String(data.id);
            inventoryType.value = data.inventory_type || 'rice';

            updateProductDisplay(data);
            status.textContent = 'QR code verified. You may now enter the stock-in quantity.';
        } catch (error) {
            clearStockIn();
            productSelect.value = '';
            inventoryType.value = '';
            stockInProductSelect.value = '';
            status.textContent = error.message || 'Invalid Product QR Code.';
        } finally {
            lookupButton.disabled = false;
        }
    });

    qrInput.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            lookupButton.click();
        }
    });

    cameraButton.addEventListener('click', async () => {
        if (typeof Html5Qrcode === 'undefined') {
            status.textContent = 'Camera scanner unavailable. Enter the QR code manually.';
            return;
        }

        try {
            if (!scanner) {
                scanner = new Html5Qrcode('qr_reader');
            }

            cameraButton.disabled = true;

            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                async decodedText => {
                    qrInput.value = decodedText;

                    try {
                        await scanner.stop();
                    } catch (error) {
                        console.error(error);
                    }

                    cameraButton.disabled = false;
                    lookupButton.click();
                },
                () => {}
            );

            status.textContent = 'Point the camera at the product QR code.';
        } catch (error) {
            cameraButton.disabled = false;
            status.textContent = 'Unable to open camera. Allow camera access or enter the code manually.';
        }
    });

    const adjustmentProduct = document.getElementById('adjustment_product_id');
    const adjustmentUnit = document.getElementById('adjustment_unit');
    const adjustmentExtraKgField =
        document.getElementById('adjustment_extra_kg_field');
    const adjustmentExtraKg =
        document.getElementById('adjustment_extra_kg');
    let initialAdjustmentUnit = adjustmentUnit.dataset.initialUnit || '';

    function updateAdjustmentUnit() {
        const option = adjustmentProduct.selectedOptions[0];
        const hasProduct = Boolean(option && option.value);

        if (!hasProduct) {
            populateSellingUnits(adjustmentUnit, [], '');
            adjustmentExtraKgField.hidden = true;
            adjustmentExtraKg.disabled = true;
            adjustmentExtraKg.value = '0';
            return;
        }

        const isRice = option.dataset.type !== 'generic';
        const baseUnit = option.dataset.baseUnit || option.dataset.unit || 'piece';
        const selectedUnit = initialAdjustmentUnit || adjustmentUnit.value;
        initialAdjustmentUnit = '';

        if (isRice) {
            populateSellingUnits(adjustmentUnit, [{ unit_name: 'kg' }], 'kg');
        } else {
            populateSellingUnits(
                adjustmentUnit,
                JSON.parse(option.dataset.sellingUnits || '[]'),
                selectedUnit || baseUnit
            );
        }

        adjustmentExtraKgField.hidden = !isRice;
        adjustmentExtraKg.disabled = !isRice;

        if (!isRice) {
            adjustmentExtraKg.value = '0';
        }
    }

    adjustmentProduct.addEventListener('change', updateAdjustmentUnit);
    updateAdjustmentUnit();

    clearStockIn();
});
</script>
</div>
@endsection