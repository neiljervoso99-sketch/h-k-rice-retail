@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
@php
    $generic = ($product->inventory_type ?? 'rice') === 'generic';
    $baseUnit = strtolower(trim((string) ($product->base_unit ?: $product->stock_unit ?: 'piece')));
    $stock = $generic
        ? (float) ($product->stock_base_quantity ?? 0)
        : (float) ($product->stock_kg ?? 0);
    $unitPrice = $generic
        ? ($product->sellingUnits->firstWhere('unit_name', $baseUnit)?->selling_price ?? $product->price_per_unit)
        : $product->price_per_kg;
    $thresholdSacks = !$generic && (float) $product->sack_weight_kg > 0
        ? (float) $product->low_stock_threshold / (float) $product->sack_weight_kg
        : 0;
@endphp

<style>
    .product-edit-page {
        max-width: 1200px;
        margin: 0 auto;
    }

    .product-edit-page .edit-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
        margin-bottom: 18px;
        border: 1px solid #e0e8e1;
        border-radius: 13px;
        background: linear-gradient(110deg, #fff, #f2f8f2);
    }

    .product-edit-page .edit-heading h1 {
        margin: 0 0 5px;
        color: #20452c;
        font-size: 25px;
    }

    .product-edit-page .edit-heading p {
        margin: 0;
        color: #68786c;
        font-size: 13px;
    }

    .product-edit-page .edit-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 16px;
        align-items: start;
    }

    .product-edit-page .edit-panel {
        min-width: 0;
        padding: 20px;
        margin-bottom: 16px;
        border: 1px solid #e0e8e1;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(27, 55, 34, .045);
    }

    .product-edit-page .edit-panel h2 {
        margin: 0 0 6px;
        color: #244d34;
        font-size: 17px;
    }

    .product-edit-page .panel-help {
        margin: 0 0 16px;
        color: #68786c;
        font-size: 12px;
        line-height: 1.5;
    }

    .product-edit-page .edit-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .product-edit-page .field {
        min-width: 0;
        margin-bottom: 10px;
    }

    .product-edit-page .field label {
        display: block;
        margin-bottom: 5px;
        color: #354c3b;
        font-size: 12px;
        font-weight: 700;
    }

    .product-edit-page .field input:not([type="checkbox"]),
    .product-edit-page .field select,
    .product-edit-page .field textarea {
        width: 100%;
        min-height: 40px;
        padding: 8px 10px;
        border: 1px solid #ccd8ca;
        border-radius: 7px;
        background: #fff;
        color: #263b2b;
        font: inherit;
        font-size: 13px;
    }

    .product-edit-page .field textarea {
        min-height: 78px;
        resize: vertical;
    }

    .product-edit-page .field input[type="file"] {
        min-height: auto;
        padding: 7px;
        background: #f9fbf8;
    }

    .product-edit-page .image-preview {
        display: block;
        width: 104px;
        height: 104px;
        margin-bottom: 10px;
        border: 1px solid #e0e7dd;
        border-radius: 9px;
        background: #f5f7f4;
        object-fit: cover;
    }

    .product-edit-page .muted {
        color: #68786c;
        font-size: 11px;
        line-height: 1.5;
    }

    .product-edit-page .selling-units-editor {
        grid-column: 1 / -1;
        padding: 14px;
        border: 1px solid #dce7da;
        border-radius: 9px;
        background: #f8faf8;
    }

    .product-edit-page .selling-unit-row {
        display: grid;
        grid-template-columns: minmax(100px, 1fr) repeat(3, minmax(90px, 1fr)) auto auto;
        gap: 8px;
        align-items: end;
        padding: 10px 0;
        border-bottom: 1px solid #e4ebe3;
    }

    .product-edit-page .selling-unit-row:last-child {
        border-bottom: 0;
    }

    .product-edit-page .selling-unit-row .field {
        margin: 0;
    }

    .product-edit-page .selling-unit-row .field label {
        font-size: 10px;
    }

    .product-edit-page .selling-unit-row .field input:not([type="checkbox"]) {
        min-height: 36px;
        padding: 7px;
    }

    .product-edit-page .selling-unit-active {
        display: flex;
        min-height: 36px;
        align-items: center;
        gap: 6px;
        color: #354c3b;
        font-size: 11px;
        white-space: nowrap;
    }

    .product-edit-page .selling-unit-active input,
    .product-edit-page .checkbox-field input {
        width: 16px;
        height: 16px;
        accent-color: #245b38;
    }

    .product-edit-page .checkbox-field label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .product-edit-page .selling-unit-remove,
    .product-edit-page .add-selling-unit {
        width: auto;
        min-height: 35px;
        padding: 7px 10px;
        border-radius: 7px;
        font-size: 11px;
    }

    .product-edit-page .selling-unit-remove {
        border: 1px solid #ead5d3;
        background: #fff;
        color: #a33630;
    }

    .product-edit-page .quick-stock-current {
        padding: 10px;
        margin: 12px 0;
        border-radius: 8px;
        background: #f1f7f1;
        color: #315a3d;
        font-size: 13px;
        font-weight: 700;
    }

    .product-edit-page .quick-stock-form .field {
        margin-bottom: 10px;
    }

    .product-edit-page .status-chip {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 20px;
        background: {{ $product->is_active ? '#e7f5e8' : '#fde9e7' }};
        color: {{ $product->is_active ? '#28633a' : '#a33630' }};
        font-size: 11px;
        font-weight: 700;
    }

    .product-edit-page .button {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        padding: 9px 14px;
        border: 1px solid #245b38;
        border-radius: 7px;
        background: #245b38;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .product-edit-page .button:hover {
        background: #17472a;
    }

    .product-edit-page .button-secondary {
        border-color: #d7dfd5;
        background: #fff;
        color: #435447;
    }

    .product-edit-page .button-secondary:hover {
        background: #f0f3ef;
    }

    .product-edit-page .edit-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid #e5ece2;
    }

    .product-edit-page .qr-panel {
        overflow: hidden;
    }

    .product-edit-page .qr-image-container {
        display: flex;
        justify-content: center;
        margin: 12px 0;
    }

    .product-edit-page .qr-image-container img,
    .product-edit-page .qr-image-container canvas {
        display: block;
        width: 140px !important;
        height: 140px !important;
    }

    :root[data-theme="dark"] .product-edit-page .edit-heading,
    :root[data-theme="dark"] .product-edit-page .edit-panel,
    :root[data-theme="dark"] .product-edit-page .selling-units-editor {
        border-color: #293149;
        background: #131b2e;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-edit-page .edit-heading h1,
    :root[data-theme="dark"] .product-edit-page .edit-panel h2 {
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-edit-page .edit-heading p,
    :root[data-theme="dark"] .product-edit-page .panel-help,
    :root[data-theme="dark"] .product-edit-page .muted {
        color: #aab6cb;
    }

    :root[data-theme="dark"] .product-edit-page .field label,
    :root[data-theme="dark"] .product-edit-page .selling-unit-active {
        color: #d3dbed;
    }

    :root[data-theme="dark"] .product-edit-page .field input:not([type="checkbox"]),
    :root[data-theme="dark"] .product-edit-page .field select,
    :root[data-theme="dark"] .product-edit-page .field textarea {
        border-color: #3a465e;
        background: #11192a;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-edit-page .field input::placeholder,
    :root[data-theme="dark"] .product-edit-page .field textarea::placeholder {
        color: #9aa5bd;
    }

    :root[data-theme="dark"] .product-edit-page .image-preview {
        border-color: #293149;
        background: #11192a;
    }

    :root[data-theme="dark"] .product-edit-page .selling-unit-row,
    :root[data-theme="dark"] .product-edit-page .edit-actions {
        border-color: #293149;
    }

    :root[data-theme="dark"] .product-edit-page .quick-stock-current {
        background: #172a22;
        color: #c9ead0;
    }

    :root[data-theme="dark"] .product-edit-page .selling-unit-remove,
    :root[data-theme="dark"] .product-edit-page .button-secondary {
        border-color: #3a465e;
        background: #1a2439;
        color: #d3dbed;
    }

    :root[data-theme="dark"] .product-edit-page ~ .notice.success,
    :root[data-theme="dark"] .product-edit-page .notice.success {
        background: #172a22;
        color: #c9ead0;
    }

    :root[data-theme="dark"] .product-edit-page .notice.error {
        background: #332027;
        color: #ffb4ad;
    }

    @media (max-width: 900px) {
        .product-edit-page .edit-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .product-edit-page .side-panels {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
    }

    @media (max-width: 600px) {
        .product-edit-page .edit-heading {
            align-items: flex-start;
            flex-direction: column;
            padding: 16px;
        }

        .product-edit-page .edit-heading h1 {
            font-size: 21px;
        }

        .product-edit-page .edit-panel {
            padding: 15px;
        }

        .product-edit-page .edit-fields,
        .product-edit-page .side-panels {
            grid-template-columns: minmax(0, 1fr);
        }

        .product-edit-page .selling-units-editor {
            grid-column: auto;
            padding: 10px;
        }

        .product-edit-page .selling-unit-row {
            grid-template-columns: minmax(0, 1fr);
            align-items: stretch;
        }
    }
</style>

<div class="product-edit-page">
    <div class="edit-heading">
        <div>
            <h1>Edit Product</h1>
            <p>{{ $product->name }} · {{ $generic ? 'General inventory' : 'Rice' }} · <span class="status-chip">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></p>
        </div>
        <a class="button button-secondary" href="{{ route('admin.products.index') }}">← Back to Products</a>
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

    <div class="edit-layout">
        <form
            method="POST"
            action="{{ route('admin.products.update', $product) }}"
            class="edit-panel product-update-form"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="_product_id" value="{{ $product->id }}">

            <h2>Product Details &amp; Pricing</h2>
            <p class="panel-help">Update product information and pricing. Inventory type and current stock are preserved.</p>

            <div class="edit-fields">
                <div class="field">
                    <label for="edit_product_name">Product Name</label>
                    <input id="edit_product_name" type="text" name="name" maxlength="255" value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="field">
                    <label for="edit_variant_size">Size / Pack Variant</label>
                    <input id="edit_variant_size" type="text" name="variant_size" maxlength="80" value="{{ old('variant_size', $product->variant_size) }}" placeholder="e.g. 5 kg, Large">
                </div>

                <div class="field">
                    <label for="edit_variant_color">Color Variant</label>
                    <input id="edit_variant_color" type="text" name="variant_color" maxlength="80" value="{{ old('variant_color', $product->variant_color) }}" placeholder="e.g. Red, Blue">
                </div>

                <div class="field">
                    <label for="edit_inventory_type">Inventory Type</label>
                    <input id="edit_inventory_type" type="text" value="{{ $generic ? 'General inventory' : 'Rice' }}" readonly>
                    <small class="muted">Inventory type is locked to preserve existing stock records.</small>
                </div>

                <div class="field">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="field">
                    <label for="edit_image">Product Image</label>
                    <img
                        class="image-preview"
                        src="{{ $product->image ? asset('storage/' . ltrim($product->image, '/')) : asset('images/default-product.svg') }}"
                        alt="{{ $product->name }}"
                        onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';"
                    >
                    <input id="edit_image" type="file" name="image" accept="image/*">
                    <small class="muted">Optional. Any supported image format, up to 10 MB.</small>
                </div>

                @if ($generic)
                    <div class="selling-units-editor">
                        <h2>Selling Units &amp; Prices</h2>
                        <p class="panel-help">The base unit must remain active with a conversion factor of 1.</p>
                        <div class="selling-unit-list" id="sellingUnitList">
                            @foreach ($product->sellingUnits as $index => $sellingUnit)
                                @php
                                    $isBaseUnit = strtolower($sellingUnit->unit_name) === $baseUnit;
                                @endphp
                                <div class="selling-unit-row" data-unit-row>
                                    <input type="hidden" name="selling_units[{{ $index }}][id]" value="{{ $sellingUnit->id }}">
                                    <div class="field">
                                        <label>Unit name</label>
                                        <input name="selling_units[{{ $index }}][unit_name]" value="{{ $sellingUnit->unit_name }}" maxlength="30" required @readonly($isBaseUnit)>
                                    </div>
                                    <div class="field">
                                        <label>Base units per unit</label>
                                        <input name="selling_units[{{ $index }}][conversion_factor]" type="number" min="0.001" max="1000000" step="0.001" value="{{ $sellingUnit->conversion_factor }}" required @readonly($isBaseUnit)>
                                    </div>
                                    <div class="field">
                                        <label>Selling price (₱)</label>
                                        <input name="selling_units[{{ $index }}][selling_price]" type="number" min="0.01" max="9999999.99" step="0.01" value="{{ $sellingUnit->selling_price }}" required>
                                    </div>
                                    <div class="field">
                                        <label>Cost (₱)</label>
                                        <input name="selling_units[{{ $index }}][cost_price]" type="number" min="0" max="9999999.99" step="0.01" value="{{ $sellingUnit->cost_price }}">
                                    </div>
                                    <label class="selling-unit-active">
                                        @if ($isBaseUnit)
                                            <input type="hidden" name="selling_units[{{ $index }}][is_active]" value="1">
                                        @endif
                                        <input type="checkbox" name="selling_units[{{ $index }}][is_active]" value="1" @checked($sellingUnit->is_active) @disabled($isBaseUnit)>
                                        Active
                                    </label>
                                    @if ($isBaseUnit)
                                        <span class="muted">Base unit</span>
                                    @else
                                        <button type="button" class="button selling-unit-remove" data-remove-unit>Remove</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="button button-secondary add-selling-unit">+ Add Selling Unit</button>
                    </div>

                    <div class="field">
                        <label for="edit_low_stock_threshold_quantity">Low Stock Threshold ({{ $baseUnit }})</label>
                        <input id="edit_low_stock_threshold_quantity" type="number" name="low_stock_threshold_quantity" min="0" max="1000000" step="0.01" value="{{ old('low_stock_threshold_quantity', $product->low_stock_threshold_quantity ?? 20) }}" required>
                    </div>
                @else
                    <div class="field">
                        <label for="edit_price_per_kg">Price per Kg (₱)</label>
                        <input id="edit_price_per_kg" type="number" name="price_per_kg" min="0" max="9999999.99" step="0.01" value="{{ old('price_per_kg', $product->price_per_kg) }}">
                    </div>
                    <div class="field">
                        <label for="edit_price_per_sack">Price per Sack (₱)</label>
                        <input id="edit_price_per_sack" type="number" name="price_per_sack" min="0" max="9999999.99" step="0.01" value="{{ old('price_per_sack', $product->price_per_sack) }}">
                    </div>
                    <div class="field">
                        <label for="edit_sack_weight">Sack Weight (Kg)</label>
                        <input id="edit_sack_weight" type="number" name="sack_weight_kg" min="1" max="1000" step="1" value="{{ old('sack_weight_kg', $product->sack_weight_kg) }}" required>
                    </div>
                    <div class="field">
                        <label for="edit_cost_per_sack">Cost per Sack (₱)</label>
                        <input id="edit_cost_per_sack" type="number" name="cost_per_sack" min="0" max="9999999.99" step="0.01" value="{{ old('cost_per_sack', $product->cost_per_sack) }}">
                    </div>
                    <div class="field">
                        <label for="edit_profit_percentage">Target Profit (%)</label>
                        <input id="edit_profit_percentage" type="number" name="profit_percentage" min="0" max="1000" step="0.01" value="{{ old('profit_percentage', $product->profit_percentage ?? 15) }}">
                    </div>
                    <div class="field">
                        <label for="edit_low_stock_threshold_sacks">Low Stock Alert (Sacks)</label>
                        <input id="edit_low_stock_threshold_sacks" type="number" name="low_stock_threshold_sacks" min="0" max="1000000" step="0.01" value="{{ old('low_stock_threshold_sacks', $thresholdSacks) }}" required>
                    </div>
                @endif
            </div>

            <div class="field checkbox-field">
                <label for="edit_price_confirmed">
                    <input type="hidden" name="price_confirmed" value="0">
                    <input id="edit_price_confirmed" type="checkbox" name="price_confirmed" value="1" @checked(old('price_confirmed', $product->price_confirmed))>
                    Confirm product pricing
                </label>
            </div>

            <div class="field checkbox-field">
                <label for="edit_is_active">
                    <input type="hidden" name="is_active" value="0">
                    <input id="edit_is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                    Active product
                </label>
            </div>

            <div class="edit-actions">
                <button class="button" type="submit">Save Changes</button>
                <a class="button button-secondary" href="{{ route('admin.products.index') }}">Cancel</a>
            </div>
        </form>

        <div class="side-panels">
            <section class="edit-panel">
                <h2>Quick Stock Adjustment</h2>
                <p class="panel-help">Adjust stock using the selected product's configured unit.</p>
                <div class="quick-stock-current">Current: {{ number_format($stock, 2) }} {{ $generic ? $baseUnit : 'kg' }}</div>
                <form method="POST" action="{{ route('admin.inventory.adjustment') }}" class="quick-stock-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="return_to" value="products">
                    <input type="hidden" name="movement_date" value="{{ now()->toDateString() }}">
                    <div class="field">
                        <label for="quick_adjustment_type">Adjustment</label>
                        <select id="quick_adjustment_type" name="adjustment_type" required>
                            <option value="adjustment_in">Add stock</option>
                            <option value="adjustment_out">Remove stock</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="quick_adjustment_quantity">Quantity</label>
                        <input id="quick_adjustment_quantity" name="quantity" type="number" min="0.01" max="1000000" step="0.01" required>
                    </div>
                    <div class="field">
                        <label for="quick_adjustment_unit">Unit</label>
                        <select id="quick_adjustment_unit" name="unit" required>
                            @if ($generic)
                                @foreach ($product->sellingUnits->where('is_active', true) as $sellingUnit)
                                    <option value="{{ strtolower($sellingUnit->unit_name) }}">{{ $sellingUnit->unit_name }}</option>
                                @endforeach
                            @else
                                <option value="kg">kg</option>
                            @endif
                        </select>
                    </div>
                    <div class="field">
                        <label for="quick_adjustment_reason">Reason</label>
                        <select id="quick_adjustment_reason" name="reason" required>
                            <option value="physical_count">Physical count</option>
                            <option value="damage">Damage</option>
                            <option value="wastage">Wastage</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="quick_adjustment_notes">Notes (optional)</label>
                        <textarea id="quick_adjustment_notes" name="notes" rows="2" maxlength="1500"></textarea>
                    </div>
                    <button class="button" type="submit">Record Adjustment</button>
                </form>
            </section>

            <section class="edit-panel qr-panel">
                <h2>Product QR Code</h2>
                <p class="panel-help">Save or print this product's QR identifier.</p>
                <form method="POST" action="{{ route('admin.products.qr-code.update', $product) }}">
                    @csrf
                    @method('PUT')
                    <div class="field">
                        <label for="edit_qr_code">QR Identifier</label>
                        <input id="edit_qr_code" name="qr_code" type="text" maxlength="255" value="{{ $product->qr_code }}" placeholder="Enter a unique QR identifier" required>
                    </div>
                    <button class="button" type="submit">Save QR Identifier</button>
                </form>
                @if ($product->qr_code)
                    <div class="qr-image-container" id="productQr" data-qr-code="{{ $product->qr_code }}"></div>
                    <button class="button button-secondary" type="button" id="printProductQr">Print QR Code</button>
                @endif
            </section>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.product-update-form');
    const list = document.getElementById('sellingUnitList');
    let nextUnitIndex = list
        ? [...list.querySelectorAll('[name^="selling_units["]')]
            .map(input => Number(input.name.match(/^selling_units\[(\d+)\]/)?.[1] || 0))
            .reduce((highest, current) => Math.max(highest, current + 1), 0)
        : 0;

    document.getElementById('edit_image')?.addEventListener('change', event => {
        const file = event.target.files?.[0];
        const preview = document.querySelector('.image-preview');

        if (!file || !preview) return;

        if (preview.dataset.previewUrl) {
            URL.revokeObjectURL(preview.dataset.previewUrl);
        }

        preview.dataset.previewUrl = URL.createObjectURL(file);
        preview.src = preview.dataset.previewUrl;
    });

    document.querySelector('.add-selling-unit')?.addEventListener('click', () => {
        if (!list || !form) return;
        const index = nextUnitIndex++;
        list.insertAdjacentHTML('beforeend', `
            <div class="selling-unit-row" data-unit-row>
                <div class="field"><label>Unit name</label><input name="selling_units[${index}][unit_name]" maxlength="30" required></div>
                <div class="field"><label>Base units per unit</label><input name="selling_units[${index}][conversion_factor]" type="number" min="0.001" max="1000000" step="0.001" required></div>
                <div class="field"><label>Selling price (₱)</label><input name="selling_units[${index}][selling_price]" type="number" min="0.01" max="9999999.99" step="0.01" required></div>
                <div class="field"><label>Cost (₱)</label><input name="selling_units[${index}][cost_price]" type="number" min="0" max="9999999.99" step="0.01"></div>
                <label class="selling-unit-active"><input type="checkbox" name="selling_units[${index}][is_active]" value="1" checked> Active</label>
                <button type="button" class="button selling-unit-remove" data-remove-unit>Remove</button>
            </div>
        `);
    });

    list?.addEventListener('click', event => {
        if (event.target.closest('[data-remove-unit]')) {
            event.target.closest('[data-unit-row]').remove();
        }
    });

    const qrContainer = document.getElementById('productQr');
    if (qrContainer && typeof QRCode !== 'undefined') {
        new QRCode(qrContainer, {
            text: qrContainer.dataset.qrCode,
            width: 140,
            height: 140,
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    document.getElementById('printProductQr')?.addEventListener('click', () => {
        const image = qrContainer?.querySelector('img');
        const canvas = qrContainer?.querySelector('canvas');
        const source = image?.src || canvas?.toDataURL('image/png');

        if (!source) {
            alert('QR image is not ready yet. Please try again.');
            return;
        }

        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            alert('Allow pop-ups to print the QR code.');
            return;
        }

        const title = @json($product->name);
        const code = qrContainer.dataset.qrCode;
        const heading = document.createElement('h2');
        heading.textContent = title;
        const qrImage = document.createElement('img');
        qrImage.src = source;
        qrImage.alt = 'Product QR Code';
        const identifier = document.createElement('p');
        identifier.textContent = code;
        printWindow.document.body.append(heading, qrImage, identifier);
        printWindow.document.head.innerHTML = '<title>Product QR Code</title>';
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endsection
