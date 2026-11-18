@extends('admin.layouts.app')

@section('title', $purchaseOrder->order_number)

@section('content')
<style>
    .purchase-order-form .field { margin-bottom: 14px; }
</style>
<div class="page-heading">
    <h1>Purchase Order {{ $purchaseOrder->order_number }}</h1>
    <p>Supplier: {{ $purchaseOrder->supplier->name }} · Status: {{ str_replace('_', ' ', ucfirst($purchaseOrder->status)) }}</p>
    <a class="button" href="{{ route('admin.purchase-orders.index') }}">Back to Purchase Orders</a>
</div>

@if (session('success'))
    <div class="notice success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="notice error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Product</th><th>Ordered</th><th>Received</th><th>Remaining</th><th>Unit</th><th>Unit Cost</th></tr></thead>
        <tbody>
            @foreach ($purchaseOrder->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ number_format((float) $item->ordered_quantity, 3) }}</td>
                    <td>{{ number_format((float) $item->received_quantity, 3) }}</td>
                    <td>{{ number_format(max(0, (float) $item->ordered_quantity - (float) $item->received_quantity), 3) }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ $item->unit_cost !== null ? '₱' . number_format((float) $item->unit_cost, 2) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if (in_array($purchaseOrder->status, ['ordered', 'partially_received'], true))
    <section class="panel purchase-order-form">
        <h2>Record Delivery</h2>
        <p>Enter only quantities received in this delivery. Previously received quantities are tracked separately.</p>
        <form method="POST" action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
            <div class="field">
                <label for="received_at">Received Date</label>
                <input id="received_at" type="date" name="received_at" value="{{ old('received_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Product</th><th>Remaining</th><th>Receive Now</th></tr></thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $item)
                            @php($remaining = max(0, (float) $item->ordered_quantity - (float) $item->received_quantity))
                            @if ($remaining > 0)
                                <tr>
                                    <td>{{ $item->product_name }} ({{ $item->unit }})</td>
                                    <td>{{ number_format($remaining, 3) }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="items[{{ $item->id }}]"
                                            min="0"
                                            max="{{ number_format($remaining, 3, '.', '') }}"
                                            step="0.001"
                                            value="{{ old('items.' . $item->id, 0) }}"
                                        >
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="field">
                <label for="receipt_notes">Receipt Notes</label>
                <textarea id="receipt_notes" name="notes" maxlength="2000">{{ old('notes') }}</textarea>
            </div>
            <button class="button" type="submit">Record Receipt and Update Stock</button>
        </form>
    </section>
@endif

<section class="panel">
    <h2>Receiving History</h2>
    @forelse ($purchaseOrder->receipts as $receipt)
        <article style="border-top:1px solid #ddd;padding:12px 0">
            <strong>Receipt #{{ $receipt->id }}</strong>
            · {{ $receipt->received_at->format('M j, Y') }}
            · {{ $receipt->receiver?->name ?? 'Former user' }}
            @if ($receipt->notes)<p>{{ $receipt->notes }}</p>@endif
            <ul>
                @foreach ($receipt->items as $receiptItem)
                    <li>{{ $receiptItem->purchaseOrderItem->product_name }}:
                        {{ number_format((float) $receiptItem->quantity, 3) }}
                        {{ $receiptItem->purchaseOrderItem->unit }}
                    </li>
                @endforeach
            </ul>
        </article>
    @empty
        <p>No deliveries have been received for this order.</p>
    @endforelse
</section>
@endsection
