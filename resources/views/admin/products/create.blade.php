@extends('admin.layouts.app')

@section('title', 'Add Product')

@section('content')
<style>
    :root[data-theme="dark"] .product-create-page {
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-create-page h1,
    :root[data-theme="dark"] .product-create-page h5 {
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-create-page .form-label,
    :root[data-theme="dark"] .product-create-page label {
        color: #d3dbed;
    }

    :root[data-theme="dark"] .product-create-page .text-muted {
        color: #aab6cb !important;
    }

    :root[data-theme="dark"] .product-create-page .form-control,
    :root[data-theme="dark"] .product-create-page input,
    :root[data-theme="dark"] .product-create-page select,
    :root[data-theme="dark"] .product-create-page textarea {
        border-color: #3a465e;
        background-color: #11192a;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-create-page .card {
        border-color: #293149;
        background: #131b2e;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .product-create-page hr {
        border-color: #3a465e;
    }

    :root[data-theme="dark"] .product-create-page .alert-danger {
        border-color: #653b42;
        background: #332027;
        color: #ffb4ad;
    }
</style>

<div class="container product-create-page">
    <h1>Add Product</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.products.store') }}"
          enctype="multipart/form-data">
        @csrf

        <input id="inventory_type" type="hidden" name="inventory_type" value="generic">

        <div class="mb-3">
            <label class="form-label">Product Type</label>
            <div class="form-control" id="detected-product-type" aria-live="polite">
                Generic Product
            </div>
            <small class="text-muted">
                Awtomatikong pipiliin ang Rice kapag ang category ay may salitang “rice” o may Rice Type. Iba pang category ay gagamit ng generic inventory at unit.
            </small>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Product Name *</label>
            <input id="name" name="name" class="form-control"
                   value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="brand" class="form-label">Brand (Optional)</label>
            <input id="brand" name="brand" class="form-control"
                   value="{{ old('brand') }}">
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input name="category" id="category" class="form-control"
                   value="{{ old('category') }}"
                   placeholder="Rice, Groceries, Drinks, Snacks, Eggs">
        </div>

        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label for="variant_size" class="form-label">Size / Pack Variant (Optional)</label>
                <input name="variant_size" id="variant_size" class="form-control" maxlength="80" value="{{ old('variant_size') }}" placeholder="e.g. 5 kg, Large">
            </div>
            <div class="col-md-6 mb-3">
                <label for="variant_color" class="form-label">Color Variant (Optional)</label>
                <input name="variant_color" id="variant_color" class="form-control" maxlength="80" value="{{ old('variant_color') }}" placeholder="e.g. Red, Blue">
            </div>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <input type="file" name="image" id="image"
                   class="form-control"
                   accept="image/*">
            <small class="text-muted">Any supported image format, up to 10 MB. You can also add or replace the image after creating the product.</small>
        </div>

        {{-- Rice-specific fields --}}
        <div id="rice-fields">
            <h5 class="mt-4">Rice Details</h5>

            <div class="mb-3">
                <label for="rice_type" class="form-label">Rice Type</label>
                <input id="rice_type" name="rice_type" class="form-control"
                       value="{{ old('rice_type') }}">
            </div>

            <div class="mb-3">
                <label for="sack_weight_kg" class="form-label">Sack Weight (kg) *</label>
                <input id="sack_weight_kg" type="number"
                       min="0.01" max="1000" step="0.01"
                       name="sack_weight_kg" class="form-control"
                       value="{{ old('sack_weight_kg') }}">
            </div>

            <div class="mb-3">
                <label for="price_per_kg" class="form-label">Price per Kilogram (₱) *</label>
                <input id="price_per_kg" type="number" step="0.01" min="0.01"
                       name="price_per_kg" class="form-control"
                       value="{{ old('price_per_kg') }}">
            </div>

            <div class="mb-3">
                <label for="price_per_sack" class="form-label">Price per Sack (₱) *</label>
                <input id="price_per_sack" type="number" step="0.01" min="0.01"
                       name="price_per_sack" class="form-control"
                       value="{{ old('price_per_sack') }}">
            </div>

            <div class="mb-3">
                <label for="cost_per_sack" class="form-label">Cost per Sack (₱) *</label>
                <input id="cost_per_sack" type="number" step="0.01" min="0.01"
                       name="cost_per_sack" class="form-control"
                       value="{{ old('cost_per_sack') }}" required>
            </div>
        </div>

        {{-- Generic product fields --}}
        <div id="generic-fields" hidden>
            <h5 class="mt-4">Inventory and Pricing</h5>

            <div class="mb-3">
                <label for="base_unit" class="form-label">
                    Base Inventory Unit *
                </label>
                <input
                    id="base_unit"
                    name="base_unit"
                    type="text"
                    class="form-control"
                    maxlength="20"
                    pattern="[a-zA-Z0-9 _-]+"
                    value="{{ old('base_unit', 'piece') }}"
                    placeholder="e.g. piece, gram, ml"
                >
                <small class="text-muted">
                    Ito ang pinakamaliit na unit kung saan sinusukat ang lahat ng stock.
                    Halimbawa: piece, gram, o ml.
                </small>
            </div>

            <div class="mb-3">
                <label for="initial_stock_quantity" class="form-label">
                    Initial Stock Quantity *
                </label>
                <input
                    id="initial_stock_quantity"
                    name="initial_stock_quantity"
                    type="number"
                    class="form-control"
                    min="0"
                    max="1000000"
                    step="0.001"
                    value="{{ old('initial_stock_quantity', 0) }}"
                >
                <small class="text-muted">
                    Ilagay ang panimulang stock gamit ang base unit sa itaas.
                </small>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Selling Units</h5>
                <button type="button" id="add-selling-unit" class="btn btn-outline-primary btn-sm">
                    + Add Selling Unit
                </button>
            </div>

            <p class="text-muted">
                Ang conversion factor ay kung ilang base units ang katumbas ng isang
                selling unit. Halimbawa, kung piece ang base unit, ang isang dozen ay 12.
            </p>

            <div id="selling-units-container">
                @php
                    $oldSellingUnits = old('selling_units', [
                        [
                            'unit_name' => 'piece',
                            'conversion_factor' => '1',
                            'selling_price' => '',
                            'cost_price' => '',
                        ],
                    ]);
                @endphp

                @foreach ($oldSellingUnits as $i => $unit)
                    <div class="card mb-3 selling-unit-row">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Selling Unit</strong>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-selling-unit">
                                    Remove
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Unit Name *</label>
                                    <input
                                        name="selling_units[{{ $i }}][unit_name]"
                                        class="form-control"
                                        maxlength="30"
                                        pattern="[a-zA-Z0-9 _-]+"
                                        value="{{ $unit['unit_name'] ?? '' }}"
                                        placeholder="e.g. piece, dozen, box"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Conversion Factor *</label>
                                    <input
                                        name="selling_units[{{ $i }}][conversion_factor]"
                                        type="number"
                                        class="form-control"
                                        min="0.000001"
                                        max="100000000"
                                        step="any"
                                        value="{{ $unit['conversion_factor'] ?? '' }}"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Selling Price (₱) *</label>
                                    <input
                                        name="selling_units[{{ $i }}][selling_price]"
                                        type="number"
                                        class="form-control"
                                        min="0.01"
                                        max="9999999.99"
                                        step="0.01"
                                        value="{{ $unit['selling_price'] ?? '' }}"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cost Price (₱) *</label>
                                    <input
                                        name="selling_units[{{ $i }}][cost_price]"
                                        type="number"
                                        class="form-control"
                                        min="0.01"
                                        max="9999999.99"
                                        step="0.01"
                                        value="{{ $unit['cost_price'] ?? '' }}"
                                        required
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-3">
            <label for="profit_percentage" class="form-label">
            Profit Percentage (%) *
            </label>
            <input id="profit_percentage" type="number" step="0.01" min="0"
                   name="profit_percentage" class="form-control"
               value="{{ old('profit_percentage', 15) }}" required>
            <small class="text-muted">
                Defaults to 15% when left blank.
            </small>
        </div>

        <div class="mb-3">
            <label for="low_stock_threshold" class="form-label">
                Low Stock Threshold (<span id="threshold-unit">kg</span>) *
            </label>
            <input id="low_stock_threshold" type="number"
                   step="0.01" min="0"
                   name="low_stock_threshold" class="form-control"
                   value="{{ old('low_stock_threshold', 20) }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description"
                      class="form-control">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="qr_code" class="form-label">Product QR Code (Optional)</label>
            <input id="qr_code" name="qr_code" class="form-control"
                   value="{{ old('qr_code') }}">
        </div>

        <div class="mb-3">
            <label for="is_active" class="form-label">Store Status *</label>
            <select id="is_active" name="is_active" class="form-select" required>
                <option value="0" @selected(old('is_active', '1') == '0')>
                    Inactive
                </option>
                <option value="1" @selected(old('is_active', '1') == '1')>
                    Active
                </option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save Product</button>
        <a href="{{ route('admin.products.index') }}"
           class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('inventory_type');
    const category = document.getElementById('category');
    const riceType = document.getElementById('rice_type');
    const detectedProductType = document.getElementById('detected-product-type');
    const riceFields = document.getElementById('rice-fields');
    const genericFields = document.getElementById('generic-fields');
    const thresholdUnit = document.getElementById('threshold-unit');
    const costPerSack = document.getElementById('cost_per_sack');

    const baseUnit = document.getElementById('base_unit');
    const initialStock = document.getElementById('initial_stock_quantity');
    const sellingUnitsContainer = document.getElementById('selling-units-container');
    const addSellingUnitButton = document.getElementById('add-selling-unit');

    const riceInputs = riceFields.querySelectorAll('input');
    const genericInputs = genericFields.querySelectorAll('input, select');

    function updateProductType() {
        const isRice = /\brice\b/i.test(category.value) || riceType.value.trim() !== '';
        type.value = isRice ? 'rice' : 'generic';
        detectedProductType.textContent = isRice ? 'Rice Product' : 'Generic Product';

        riceFields.hidden = !isRice;
        genericFields.hidden = isRice;

        riceInputs.forEach(input => {
            input.disabled = !isRice;
            input.required = isRice && [
                'sack_weight_kg',
                'price_per_kg',
                'price_per_sack',
                'cost_per_sack'
            ].includes(input.id);
        });

        genericInputs.forEach(input => {
            input.disabled = isRice;
        });

        baseUnit.required = !isRice;
        initialStock.required = !isRice;

        sellingUnitsContainer
            .querySelectorAll('input')
            .forEach(input => {
                input.disabled = isRice;
                input.required = !isRice;
            });

        costPerSack.required = isRice;

        thresholdUnit.textContent = isRice
            ? 'kg'
            : (baseUnit.value.trim() || 'base unit');
    }

    function reindexSellingUnits() {
        const rows = sellingUnitsContainer.querySelectorAll('.selling-unit-row');

        rows.forEach((row, index) => {
            row.querySelectorAll('input').forEach(input => {
                input.name = input.name.replace(
                    /selling_units\[\d+\]/,
                    `selling_units[${index}]`
                );
            });

            const removeButton = row.querySelector('.remove-selling-unit');
            removeButton.disabled = rows.length <= 1;
        });
    }

    addSellingUnitButton.addEventListener('click', function () {
        const rows = sellingUnitsContainer.querySelectorAll('.selling-unit-row');

        if (rows.length >= 20) {
            alert('Maximum na ang 20 selling units.');
            return;
        }

        const index = rows.length;
        const row = document.createElement('div');

        row.className = 'card mb-3 selling-unit-row';
        row.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <strong>Selling Unit</strong>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-selling-unit">
                        Remove
                    </button>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit Name *</label>
                        <input
                            name="selling_units[${index}][unit_name]"
                            class="form-control"
                            maxlength="30"
                            pattern="[a-zA-Z0-9 _-]+"
                            placeholder="e.g. dozen, box"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Conversion Factor *</label>
                        <input
                            name="selling_units[${index}][conversion_factor]"
                            type="number"
                            class="form-control"
                            min="0.000001"
                            max="100000000"
                            step="any"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Selling Price (₱) *</label>
                        <input
                            name="selling_units[${index}][selling_price]"
                            type="number"
                            class="form-control"
                            min="0.01"
                            max="9999999.99"
                            step="0.01"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cost Price (₱) *</label>
                        <input
                            name="selling_units[${index}][cost_price]"
                            type="number"
                            class="form-control"
                            min="0.01"
                            max="9999999.99"
                            step="0.01"
                            required
                        >
                    </div>
                </div>
            </div>
        `;

        sellingUnitsContainer.appendChild(row);
        updateProductType();
        reindexSellingUnits();
    });

    sellingUnitsContainer.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-selling-unit');

        if (!button) return;

        const rows = sellingUnitsContainer.querySelectorAll('.selling-unit-row');

        if (rows.length <= 1) {
            alert('Kailangan ng kahit isang selling unit.');
            return;
        }

        button.closest('.selling-unit-row').remove();
        reindexSellingUnits();
    });

    baseUnit.addEventListener('input', function () {
        if (type.value === 'generic') {
            thresholdUnit.textContent = this.value.trim() || 'base unit';
        }
    });

    category.addEventListener('input', updateProductType);
    riceType.addEventListener('input', updateProductType);

    updateProductType();
    reindexSellingUnits();
});
</script>
@endsection
