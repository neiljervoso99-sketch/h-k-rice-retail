
@extends('rider.layouts.app')

@section('title', 'Delivery Details')

@section('content')
@php
    $customerAddress = collect([
        $order->address_line,
        $order->barangay,
        $order->municipality,
        $order->province,
        $order->postal_code,
    ])->filter(fn ($value) => filled($value))->implode(', ');

    $hasCoordinates = is_numeric($order->delivery_latitude)
        && is_numeric($order->delivery_longitude);

    $mapDestination = $hasCoordinates
        ? $order->delivery_latitude . ',' . $order->delivery_longitude
        : $customerAddress;

    $googleMapsUrl = 'https://www.google.com/maps/search/?api=1&query='
        . urlencode($mapDestination);

    $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination='
        . urlencode($mapDestination)
        . '&travelmode=driving';
@endphp

<div class="delivery-page">
    <a href="{{ route('rider.dashboard') }}" class="back-link">
        &larr; Back to Dashboard
    </a>

    <div class="page-title">
        <div>
            <h1>Delivery Details</h1>
            <p>Order {{ $order->order_number }}</p>
        </div>

        <span class="status-badge {{ $order->status === 'completed' ? 'done' : 'active' }}">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    @if (session('success'))
        <div class="notice success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="notice error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="content-grid">
        <section class="panel">
            <h2>Customer Information</h2>

            <div class="info-row">
                <span>Recipient</span>
                <strong>{{ $order->recipient_name ?: $order->customer?->name ?? 'N/A' }}</strong>
            </div>

            <div class="info-row">
                <span>Phone Number</span>
                <strong>{{ $order->phone ?: 'Not provided' }}</strong>
            </div>

            <div class="info-row">
                <span>Delivery Address</span>
                <strong>{{ $customerAddress ?: 'No address provided' }}</strong>
            </div>

            @if ($customerAddress || $hasCoordinates)
                <div class="map-actions">
                    <a
                        href="{{ $googleMapsUrl }}"
                        class="btn secondary"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        View on Google Maps
                    </a>

                    <a
                        href="{{ $directionsUrl }}"
                        class="btn"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Get Directions
                    </a>
                </div>

                @if ($hasCoordinates)
                    <p class="muted map-note">
                        Using the customer's saved map coordinates.
                    </p>
                @else
                    <p class="muted map-note">
                        Destination is based on the customer's delivery address.
                    </p>
                @endif
            @else
                <p class="muted map-note">
                    No delivery address or coordinates are available for this order.
                </p>
            @endif

            @if ($order->delivery_notes)
                <div class="info-row">
                    <span>Delivery Notes</span>
                    <strong>{{ $order->delivery_notes }}</strong>
                </div>
            @endif
        </section>

        <section class="panel">
            <h2>Order Items</h2>

            @forelse ($order->items as $item)
                <div class="item-row">
                    <div>
                        <strong>{{ $item->product_name }}</strong>

                        <div class="muted">
                            {{ $item->quantity }} {{ $item->unit_type }}

                            @if ($item->quantity_kg)
                                ({{ $item->quantity_kg }} kg)
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="muted">No items found for this order.</p>
            @endforelse

            <div class="total-row">
                <span>Order Total</span>
                <strong>₱{{ number_format((float) $order->total, 2) }}</strong>
            </div>

            <div class="info-row">
                <span>Payment Method</span>
                <strong>{{ strtoupper($order->payment_method ?? 'N/A') }}</strong>
            </div>
        </section>
    </div>

    <section class="panel proof-panel">
        <h2>Delivery Proof</h2>
        <p>Upload a photo showing that the order was delivered to the customer.</p>

        @if ($order->delivery_proof_path)
            <div class="uploaded-info">
                <strong>Proof photo uploaded</strong>

                <span class="muted">
                    {{ $order->delivery_proof_uploaded_at?->format('M d, Y h:i A') ?? 'Upload time unavailable' }}
                </span>

                <p>
                    <a
                        class="btn secondary"
                        href="{{ route('rider.orders.proof.view', $order) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        View Uploaded Photo
                    </a>
                </p>
            </div>
        @endif

        @if ($order->status === 'accepted')
            <div class="upload-grid">
                <form
                    method="POST"
                    action="{{ route('rider.orders.proof.upload', $order) }}"
                    enctype="multipart/form-data"
                    class="upload-card"
                >
                    @csrf

                    <h3>Upload Existing Photo</h3>

                    <label for="proof-upload">Choose an image</label>

                    <input
                        id="proof-upload"
                        type="file"
                        name="proof"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >

                    <p class="muted">JPG, PNG, or WebP. Maximum size: 5 MB.</p>

                    <button class="btn" type="submit">Upload Photo</button>
                </form>

                <form
                    method="POST"
                    action="{{ route('rider.orders.proof.upload', $order) }}"
                    enctype="multipart/form-data"
                    class="upload-card"
                >
                    @csrf

                    <h3>Take a Picture</h3>

                    <label for="proof-camera">Open your camera</label>

                    <input
                        id="proof-camera"
                        type="file"
                        name="proof"
                        accept="image/*"
                        capture="environment"
                        required
                    >

                    <p class="muted">On supported phones, this opens the rear camera.</p>

                    <button class="btn" type="submit">Upload Camera Photo</button>
                </form>
            </div>

            <form
                method="POST"
                action="{{ route('rider.orders.complete', $order) }}"
                class="complete-form"
                onsubmit="return confirm('Confirm that this order has been delivered?');"
            >
                @csrf

                <button
                    class="btn complete-btn"
                    type="submit"
                    {{ $order->delivery_proof_path ? '' : 'disabled' }}
                >
                    Complete Delivery
                </button>

                @if (!$order->delivery_proof_path)
                    <p class="muted">
                        Upload a delivery photo before completing this order.
                    </p>
                @endif
            </form>
        @else
            <p class="muted">This delivery is no longer active.</p>
        @endif
    </section>
</div>

<style>
.delivery-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px;
    color: #263b2b;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
    padding: 11px 18px;
    background: #245333;
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    border-radius: 8px;
    border: 1px solid #245333;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
    transition: background 0.2s ease, transform 0.2s ease;
}

.back-link:hover {
    background: #183b24;
    color: #ffffff;
    transform: translateY(-1px);
}

.back-link:focus-visible {
    outline: 3px solid #9ac7a5;
    outline-offset: 3px;
}

.page-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 24px;
}

.page-title h1 {
    margin: 0;
    font-size: 27px;
}

.page-title p {
    margin: 7px 0 0;
    color: #657267;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 18px;
}

.panel {
    background: white;
    border: 1px solid #dfe6dc;
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 18px;
}

.panel h2 {
    margin: 0 0 18px;
    font-size: 20px;
}

.info-row,
.total-row {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    padding: 12px 0;
    border-bottom: 1px solid #e5eae2;
}

.info-row span,
.total-row span {
    color: #657267;
    flex: 0 0 35%;
}

.info-row strong {
    text-align: right;
    overflow-wrap: anywhere;
}

.item-row {
    padding: 13px 0;
    border-bottom: 1px solid #e5eae2;
}

.muted {
    color: #657267;
    font-size: 13px;
}

.total-row {
    margin-top: 8px;
    border-bottom: 0;
    font-size: 17px;
}

.status-badge {
    padding: 6px 11px;
    border-radius: 20px;
    background: #e8f1e5;
    color: #245333;
    white-space: nowrap;
}

.status-badge.done {
    background: #e2f3e7;
    color: #176b35;
}

.notice {
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
}

.notice p {
    margin: 4px 0;
}

.notice.success {
    background: #e2f3e7;
    color: #176b35;
}

.notice.error {
    background: #fde8e7;
    color: #922b25;
}

.map-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 14px;
}

.map-actions .btn {
    text-align: center;
}

.map-note {
    margin-top: 10px;
}

.upload-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.upload-card {
    border: 1px solid #dfe6dc;
    border-radius: 10px;
    padding: 18px;
}

.upload-card h3 {
    margin: 0 0 14px;
}

.upload-card label {
    display: block;
    font-weight: 700;
    margin-bottom: 8px;
}

.upload-card input {
    display: block;
    width: 100%;
    max-width: 100%;
    margin-bottom: 12px;
}

.btn {
    display: inline-block;
    border: 0;
    border-radius: 7px;
    padding: 11px 15px;
    background: #245333;
    color: white;
    text-decoration: none;
    font-weight: 700;
    cursor: pointer;
}

.btn:hover {
    background: #193d25;
}

.btn.secondary {
    background: #e8f1e5;
    color: #245333;
}

.btn:disabled {
    background: #aab5aa;
    cursor: not-allowed;
}

.complete-form {
    margin-top: 22px;
    padding-top: 20px;
    border-top: 1px solid #dfe6dc;
}

.complete-btn {
    min-width: 190px;
}

@media (max-width: 750px) {
    .delivery-page {
        padding: 14px;
    }

    .content-grid,
    .upload-grid {
        grid-template-columns: 1fr;
    }

    .page-title h1 {
        font-size: 23px;
    }

    .info-row {
        flex-direction: column;
        gap: 5px;
    }

    .info-row strong {
        text-align: left;
    }
}

@media (max-width: 480px) {
    .map-actions {
        flex-direction: column;
    }

    .map-actions .btn {
        width: 100%;
        box-sizing: border-box;
    }

    .complete-btn {
        width: 100%;
        min-height: 44px;
    }

    .panel,
    .upload-card {
        padding: 15px;
    }

    .upload-card input[type="file"] {
        font-size: 14px;
    }
}
</style>
@endsection
