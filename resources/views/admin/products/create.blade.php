@extends('admin.layouts.app')

@section('title', 'Add Product')

@section('content')
@php
    $initialRiceProduct = preg_match('/\brice\b/i', (string) old('category', '')) === 1
        || filled(old('rice_type'));
    $initialInventoryType = $initialRiceProduct ? 'rice' : 'generic';
    $baseUnit = strtolower(trim((string) old('base_unit', 'piece')));
    $oldSellingUnits = old('selling_units', []);
    $baseSellingUnit = null;

    foreach ($oldSellingUnits as $key => $unit) {
        if (
            strtolower(trim((string) ($unit['unit_name'] ?? ''))) === $baseUnit
            && abs((float) ($unit['conversion_factor'] ?? 0) - 1) < 0.000001
        ) {
            $baseSellingUnit = $unit;
            unset($oldSellingUnits[$key]);
            break;
        }
    }

    $oldSellingUnits = array_values($oldSellingUnits);
@endphp

<style>
    .product-create-page {
        max-width: 1080px;
        margin: 0 auto;
    }

    .product-create-page .create-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .product-create-page .create-heading h1 {
        margin: 0 0 6px;
        color: #203f2b;
        font-size: 26px;
    }

    .product-create-page .create-heading p,
    .product-create-page .field-help {
        margin: 0;
        color: #657568;
        font-size: 13px;
        line-height: 1.5;
    }

    .product-create-page .form-panel {
        padding: 22px;
        margin-bottom: 16px;
        border: 1px solid #e1e8df;
        border-radius: 12px;
        background: #fff;
    }

    .product-create-page .form-panel h2 {
        margin: 0 0 6px;
        color: #244d34;
        font-size: 18px;
    }

    .product-create-page .panel-help {
        margin: 0 0 18px;
        color: #657568;
        font-size: 13px;
        line-height: 1.5;
    }

    .product-create-page .fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .product-create-page .field {
        min-width: 0;
    }

    .product-create-page .field label {
        display: block;
        margin-bottom: 6px;
        color: #354c3b;
        font-size: 13px;
        font-weight: 700;
    }

    .product-create-page .field input,
    .product-create-page .field select,
    .product-create-page .field textarea {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #cbd5ca;
        border-radius: 7px;
        background: #fff;
        color: #263b2b;
    }

    .product-create-page .field textarea {
        min-height: 90px;
        resize: vertical;
    }

    .product-create-page .field-help {
        display: block;
        margin-top: 5px;
    }

    .product-create-page .product-type-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 4px 10px;
        border-radius: 20px;
        background: #edf4ec;
        color: #315a3d;
        font-size: 12px;
        font-weight: 700;
    }

    .product-create-page .selling-unit-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
        align-items: end;
        gap: 10px;
        padding: 14px;
        margin: 12px 0;
        border: 1px solid #e1e8df;
        border-radius: 9px;
        background: #f9fbf9;
    }

    .product-create-page .optional-settings {
        margin-top: 14px;
        border: 1px solid #e1e8df;
        border-radius: 9px;
        background: #fff;
    }

    .product-create-page .optional-settings summary {
        padding: 13px 15px;
        color: #315a3d;
        font-weight: 700;
        cursor: pointer;
    }

    .product-create-page .optional-settings-content {
        padding: 4px 15px 15px;
    }

    .product-create-page .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    :root[data-theme="dark"] .product-create-page .create-heading h1,
    :root[data-theme="dark"] .product-create-page .form-panel h2 {
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-create-page .form-panel,
    :root[data-theme="dark"] .product-create-page .optional-settings {
        border-color: #293149;
        background: #131b2e;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-create-page .field label,
    :root[data-theme="dark"] .product-create-page .optional-settings summary {
        color: #d3dbed;
    }

    :root[data-theme="dark"] .product-create-page .create-heading p,
    :root[data-theme="dark"] .product-create-page .panel-help,
    :root[data-theme="dark"] .product-create-page .field-help {
        color: #aab6cb;
    }

    :root[data-theme="dark"] .product-create-page .field input,
    :root[data-theme="dark"] .product-create-page .field select,
    :root[data-theme="dark"] .product-create-page .field textarea {
        border-color: #3a465e;
        background: #11192a;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-create-page .selling-unit-row {
        border-color: #293149;
        background: #10182a;
    }

    :root[data-theme="dark"] .product-create-page .notice.error {
        border-color: #653b42;
        background: #332027;
        color: #ffb4ad;
    }

    @media (max-width: 760px) {
        .product-create-page .selling-unit-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 560px) {
        .product-create-page .fields,
        .product-create-page .selling-unit-row {
            grid-template-columns: minmax(0, 1fr);
        }

        .product-create-page .create-heading {
            display: block;
        }
    }
</style>

<div class="product-create-page">
    <div class="create-heading">
        <div>
            <h1>Add Product</h1>
            <p>Enter the basic product details and pricing. The inventory type is selected automatically.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="button button-secondary">Cancel</a>
    </div>

    @if ($errors->any())
        <div class="notice error" role="alert">
            <strong>Please review the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        <input id="inventory_type" type="hidden" name="inventory_type" value="{{ $initialInventoryType }}">

        <section class="form-panel">
            <h2>Product Details</h2>
            <p class="panel-help">
                Product type:
                <span class="product-type-badge" id="detected-product-type" aria-live="polite">
                    {{ $initialRiceProduct ? 'Rice Product' : 'Generic Product' }}
                </span>
                All other categories use generic inventory.
            </p>

            <div class="fields">
                <div class="field">
                    <label for="name">Product Name *</label>
                    <input id="name" name="name" maxlength="255" value="{{ old('name') }}" required>
                </div>
                <div class="field">
                    <label for="category">Category</label>
                    <input id="category" name="category" maxlength="255" value="{{ old('category') }}" placeholder="Rice, Groceries, Drinks">
                    <small class="field-help">A category containing “rice” automatically selects Rice as the product type.</small>
                </div>
                <div class="field">
                    <label for="rice_type">Rice Type (optional)</label>
                    <input id="rice_type" name="rice_type" maxlength="255" value="{{ old('rice_type') }}" placeholder="Jasmine, Dinorado">
                    <small class="field-help">Entering a rice type automatically selects Rice as the product type.</small>
                </div>
                <div class="field">
                    <label for="brand">Brand (optional)</label>
                    <input id="brand" name="brand" maxlength="255" value="{{ old('brand') }}">
                </div>
                <div class="field">
                    <label for="image">Product Image (optional)</label>
                    <input id="image" type="file" name="image" accept="image/*">
                    <small class="field-help">Supported image formats, up to 10 MB.</small>
                </div>
            </div>
        </section>

        <section id="rice-fields" class="form-panel" @if (!$initialRiceProduct) hidden @endif>
            <h2>Rice Pricing</h2>
            <p class="panel-help">Enter the sack weight and selling prices by kilogram and sack.</p>
            <div class="fields">
                <div class="field">
                    <label for="sack_weight_kg">Sack Weight (kg) *</label>
                    <input id="sack_weight_kg" type="number" min="1" max="1000" step="1" name="sack_weight_kg" value="{{ old('sack_weight_kg') }}">
                </div>
                <div class="field">
                    <label for="price_per_kg">Selling Price per kg (₱) *</label>
                    <input id="price_per_kg" type="number" min="0.01" max="9999999.99" step="0.01" name="price_per_kg" value="{{ old('price_per_kg') }}">
                </div>
                <div class="field">
                    <label for="price_per_sack">Selling Price per Sack (₱) *</label>
                    <input id="price_per_sack" type="number" min="0.01" max="9999999.99" step="0.01" name="price_per_sack" value="{{ old('price_per_sack') }}">
                </div>
                <div class="field">
                    <label for="cost_per_sack">Cost per Sack (₱) *</label>
                    <input id="cost_per_sack" type="number" min="0.01" max="9999999.99" step="0.01" name="cost_per_sack" value="{{ old('cost_per_sack') }}">
                </div>
            </div>
        </section>

        <section id="generic-fields" class="form-panel" @if ($initialRiceProduct) hidden @endif>
            <h2>Inventory and Pricing</h2>
            <p class="panel-help">Inventory is measured in the base unit. You can create a product without adding extra selling units.</p>

            <div class="fields">
                <div class="field">
                    <label for="base_unit">Base Inventory Unit *</label>
                    <input id="base_unit" name="base_unit" maxlength="20" pattern="[a-zA-Z0-9 _-]+" value="{{ $baseUnit }}" placeholder="piece, gram, ml">
                    <small class="field-help">The smallest unit used to measure inventory.</small>
                </div>
                <div class="field">
                    <label for="initial_stock_quantity">Initial Stock (<span id="base-unit-stock-label">{{ $baseUnit ?: 'base unit' }}</span>) *</label>
                    <input id="initial_stock_quantity" name="initial_stock_quantity" type="number" min="0" max="1000000" step="0.001" value="{{ old('initial_stock_quantity', 0) }}">
                </div>
                <div class="field">
                    <label for="base-selling-price">Selling Price per <span id="base-unit-price-label">{{ $baseUnit ?: 'base unit' }}</span> (₱) *</label>
                    <input id="base-selling-price" name="selling_units[0][selling_price]" type="number" min="0.01" max="9999999.99" step="0.01" value="{{ old('selling_units.0.selling_price', $baseSellingUnit['selling_price'] ?? '') }}">
                </div>
                <div class="field">
                    <label for="base-cost-price">Cost per <span id="base-unit-cost-label">{{ $baseUnit ?: 'base unit' }}</span> (₱) *</label>
                    <input id="base-cost-price" name="selling_units[0][cost_price]" type="number" min="0.01" max="9999999.99" step="0.01" value="{{ old('selling_units.0.cost_price', $baseSellingUnit['cost_price'] ?? '') }}">
                </div>
            </div>

            <input id="base-selling-unit-name" type="hidden" name="selling_units[0][unit_name]" value="{{ $baseUnit }}">
            <input type="hidden" name="selling_units[0][conversion_factor]" value="1">

            <details class="optional-settings" id="extra-selling-units">
                <summary>Add another selling unit (optional)</summary>
                <div class="optional-settings-content">
                    <p class="panel-help">Example: one dozen equals 12 pieces. Skip this if you only sell by the base unit.</p>
                    <div id="selling-units-container">
                        @foreach ($oldSellingUnits as $index => $unit)
                            @php($fieldIndex = $index + 1)
                            <div class="selling-unit-row">
                                <div class="field">
                                    <label>Selling Unit Name *</label>
                                    <input name="selling_units[{{ $fieldIndex }}][unit_name]" maxlength="30" pattern="[a-zA-Z0-9 _-]+" value="{{ $unit['unit_name'] ?? '' }}" placeholder="dozen, box" required>
                                </div>
                                <div class="field">
                                    <label>Base Units per Selling Unit *</label>
                                    <input name="selling_units[{{ $fieldIndex }}][conversion_factor]" type="number" min="0.000001" max="100000000" step="any" value="{{ $unit['conversion_factor'] ?? '' }}" required>
                                </div>
                                <div class="field">
                                    <label>Selling Price (₱) *</label>
                                    <input name="selling_units[{{ $fieldIndex }}][selling_price]" type="number" min="0.01" max="9999999.99" step="0.01" value="{{ $unit['selling_price'] ?? '' }}" required>
                                </div>
                                <div class="field">
                                    <label>Cost Price (₱) *</label>
                                    <input name="selling_units[{{ $fieldIndex }}][cost_price]" type="number" min="0.01" max="9999999.99" step="0.01" value="{{ $unit['cost_price'] ?? '' }}" required>
                                </div>
                                <button type="button" class="button button-danger remove-selling-unit">Remove</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-selling-unit" class="button button-secondary">+ Add Selling Unit</button>
                </div>
            </details>
        </section>

        <details class="optional-settings" @if ($errors->any()) open @endif>
            <summary>More settings (optional)</summary>
            <div class="optional-settings-content">
                <div class="fields">
                    <div class="field">
                        <label for="variant_size">Size / Pack Variant (optional)</label>
                        <input id="variant_size" name="variant_size" maxlength="80" value="{{ old('variant_size') }}" placeholder="5 kg, Large">
                    </div>
                    <div class="field">
                        <label for="variant_color">Color Variant (optional)</label>
                        <input id="variant_color" name="variant_color" maxlength="80" value="{{ old('variant_color') }}" placeholder="Red, Blue">
                    </div>
                    <div class="field">
                        <label for="profit_percentage">Profit Percentage (%)</label>
                        <input id="profit_percentage" type="number" min="0" max="1000" step="0.01" name="profit_percentage" value="{{ old('profit_percentage', 15) }}">
                        <small class="field-help">Defaults to 15% if left unchanged.</small>
                    </div>
                    <div class="field">
                        <label for="low_stock_threshold">Low Stock Alert Threshold (<span id="threshold-unit">{{ $initialRiceProduct ? 'kg' : ($baseUnit ?: 'base unit') }}</span>) *</label>
                        <input id="low_stock_threshold" type="number" min="0" max="1000000" step="0.01" name="low_stock_threshold" value="{{ old('low_stock_threshold', 20) }}" required>
                    </div>
                    <div class="field">
                        <label for="description">Description</label>
                        <textarea id="description" name="description">{{ old('description') }}</textarea>
                    </div>
                    <div class="field">
                        <label for="qr_code">Product QR Code</label>
                        <input id="qr_code" name="qr_code" maxlength="255" value="{{ old('qr_code') }}">
                    </div>
                    <div class="field">
                        <label for="is_active">Store Status</label>
                        <select id="is_active" name="is_active" required>
                            <option value="1" @selected(old('is_active', '1') == '1')>Active</option>
                            <option value="0" @selected(old('is_active', '1') == '0')>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </details>

        <div class="form-actions">
            <button type="submit" class="button">Save Product</button>
            <a href="{{ route('admin.products.index') }}" class="button button-secondary">Cancel</a>
        </div>
    </form>
</div>

<template id="selling-unit-template">
    <div class="selling-unit-row">
        <div class="field">
            <label>Selling Unit Name *</label>
            <input data-field="unit_name" maxlength="30" pattern="[a-zA-Z0-9 _-]+" placeholder="dozen, box" required>
        </div>
        <div class="field">
            <label>Base Units per Selling Unit *</label>
            <input data-field="conversion_factor" type="number" min="0.000001" max="100000000" step="any" required>
        </div>
        <div class="field">
            <label>Selling Price (₱) *</label>
            <input data-field="selling_price" type="number" min="0.01" max="9999999.99" step="0.01" required>
        </div>
        <div class="field">
            <label>Cost Price (₱) *</label>
            <input data-field="cost_price" type="number" min="0.01" max="9999999.99" step="0.01" required>
        </div>
        <button type="button" class="button button-danger remove-selling-unit">Remove</button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeInput = document.getElementById('inventory_type');
    const categoryInput = document.getElementById('category');
    const riceTypeInput = document.getElementById('rice_type');
    const typeBadge = document.getElementById('detected-product-type');
    const riceFields = document.getElementById('rice-fields');
    const genericFields = document.getElementById('generic-fields');
    const baseUnitInput = document.getElementById('base_unit');
    const baseUnitName = document.getElementById('base-selling-unit-name');
    const thresholdUnit = document.getElementById('threshold-unit');
    const sellingUnits = document.getElementById('selling-units-container');

    const updateType = () => {
        const isRice = /\brice\b/i.test(categoryInput.value)
            || (riceTypeInput && riceTypeInput.value.trim() !== '');
        typeInput.value = isRice ? 'rice' : 'generic';
        typeBadge.textContent = isRice ? 'Rice Product' : 'Generic Product';
        riceFields.hidden = !isRice;
        genericFields.hidden = isRice;

        riceFields.querySelectorAll('input, select, textarea').forEach((input) => {
            input.disabled = !isRice;
            input.required = isRice && [
                'sack_weight_kg',
                'price_per_kg',
                'price_per_sack',
                'cost_per_sack',
            ].includes(input.name);
        });

        genericFields.querySelectorAll('input, select, textarea').forEach((input) => {
            input.disabled = isRice;
        });

        if (baseUnitInput) {
            baseUnitInput.required = !isRice;
            document.getElementById('initial_stock_quantity').required = !isRice;
            document.getElementById('base-selling-price').required = !isRice;
            document.getElementById('base-cost-price').required = !isRice;
            baseUnitName.value = baseUnitInput.value.trim().toLowerCase();
            document.getElementById('base-unit-stock-label').textContent =
                baseUnitInput.value.trim() || 'base unit';
            document.getElementById('base-unit-price-label').textContent =
                baseUnitInput.value.trim() || 'base unit';
            document.getElementById('base-unit-cost-label').textContent =
                baseUnitInput.value.trim() || 'base unit';
        }

        thresholdUnit.textContent = isRice
            ? 'kg'
            : (baseUnitInput.value.trim() || 'base unit');
    };

    const reindexSellingUnits = () => {
        sellingUnits.querySelectorAll('.selling-unit-row').forEach((row, index) => {
            const fieldIndex = index + 1;
            row.querySelectorAll('[data-field], [name^="selling_units["]').forEach((input) => {
                const field = input.dataset.field
                    || input.name.match(/^selling_units\[\d+\]\[([^\]]+)\]$/)?.[1];
                if (field) input.name = `selling_units[${fieldIndex}][${field}]`;
            });
        });
    };

    document.getElementById('add-selling-unit').addEventListener('click', () => {
        if (sellingUnits.children.length >= 19) {
            alert('A product can have up to 20 selling units.');
            return;
        }

        const row = document.getElementById('selling-unit-template')
            .content.firstElementChild.cloneNode(true);
        sellingUnits.appendChild(row);
        reindexSellingUnits();
    });

    sellingUnits.addEventListener('click', (event) => {
        const button = event.target.closest('.remove-selling-unit');
        if (!button) return;
        button.closest('.selling-unit-row').remove();
        reindexSellingUnits();
    });

    categoryInput.addEventListener('input', updateType);
    if (riceTypeInput) riceTypeInput.addEventListener('input', updateType);
    if (baseUnitInput) baseUnitInput.addEventListener('input', updateType);

    updateType();
    reindexSellingUnits();
});
</script>
@endsection
