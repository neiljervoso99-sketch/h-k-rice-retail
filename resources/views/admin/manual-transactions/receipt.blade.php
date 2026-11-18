@extends('admin.layouts.app')

@section('title', 'Transaction Receipt')

@section('content')
<style>
    .pos-receipt {
        width: min(100%, 420px);
        margin: 24px auto;
        padding: 28px;
        color: #17231a;
        background: #fff;
        border: 1px solid #dfe7df;
        border-radius: 12px;
        font-family: Arial, sans-serif;
    }

    .pos-receipt h1,
    .pos-receipt p {
        margin: 0;
    }

    .pos-receipt .receipt-header,
    .pos-receipt .receipt-total {
        text-align: center;
    }

    .pos-receipt .receipt-header {
        padding-bottom: 12px;
        border-bottom: 1px dashed #8a978c;
    }

    .pos-receipt .receipt-logo {
        display: block;
        width: 18mm;
        height: 18mm;
        margin: 0 auto 6px;
        object-fit: contain;
    }

    .pos-receipt .receipt-header h1 {
        margin-bottom: 3px;
        font-size: 17px;
        line-height: 1.2;
    }

    .pos-receipt .receipt-store-contact {
        margin-top: 5px;
        font-size: 10px;
        line-height: 1.35;
    }

    .pos-receipt .receipt-meta,
    .pos-receipt .receipt-payment {
        padding: 10px 0;
        border-bottom: 1px dashed #8a978c;
    }

    .pos-receipt .receipt-meta div,
    .pos-receipt .receipt-payment div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin: 4px 0;
    }

    .pos-receipt table {
        width: 100%;
            margin: 9px 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .pos-receipt th:first-child,
    .pos-receipt td:first-child { width: 68%; overflow-wrap: anywhere; }
    .pos-receipt th:last-child,
    .pos-receipt td:last-child { width: 32%; }

    .pos-receipt th,
    .pos-receipt td {
        padding: 5px 1px;
        text-align: left;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    .pos-receipt th:last-child,
    .pos-receipt td:last-child {
        text-align: right;
        white-space: nowrap;
    }

    .pos-receipt .receipt-total {
        padding: 9px 0;
        border-top: 1px dashed #8a978c;
        border-bottom: 1px dashed #8a978c;
        font-size: 17px;
        font-weight: 700;
    }

    .pos-receipt .receipt-subtotal {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 5px 0;
    }

    .pos-receipt .receipt-payment h2 {
        margin: 0 0 6px;
        font-size: 11px;
        text-transform: uppercase;
    }

    .pos-receipt .receipt-item-name { display: block; font-weight: 700; }
    .pos-receipt .receipt-item-detail { display: block; margin-top: 2px; font-size: 9px; }

    .pos-receipt .receipt-thanks {
        padding-top: 10px;
        text-align: center;
    }

    .receipt-print-settings {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px solid #dfe7df;
        border-radius: 6px;
        background: #fff;
        color: #17231a;
        font-size: 13px;
        font-weight: 700;
    }

    .receipt-print-settings select {
        min-height: 36px;
        padding: 5px 8px;
        border: 1px solid #b8c5b9;
        border-radius: 5px;
        background: #fff;
        color: #17231a;
        font: inherit;
    }

    @page { margin: 0; }

    :root[data-theme="dark"] .admin-main .pos-receipt {
        border-color: #dfe7df !important;
        background: #fff !important;
        color: #17231a !important;
    }

    :root[data-theme="dark"] .admin-main .pos-receipt h1,
    :root[data-theme="dark"] .admin-main .pos-receipt p,
    :root[data-theme="dark"] .admin-main .pos-receipt span,
    :root[data-theme="dark"] .admin-main .pos-receipt strong,
    :root[data-theme="dark"] .admin-main .pos-receipt small,
    :root[data-theme="dark"] .admin-main .pos-receipt td {
        color: #17231a !important;
    }

    :root[data-theme="dark"] .admin-main .pos-receipt th {
        border: 0;
        background: #fff !important;
        color: #17231a !important;
    }

    @media print {
        body {
            background: #fff !important;
        }

        body * {
            visibility: hidden !important;
        }

        .pos-receipt,
        .pos-receipt * {
            visibility: visible !important;
        }

        .pos-receipt {
            position: absolute;
            top: 0;
            left: 0;
            width: 80mm;
            max-width: 80mm;
            margin: 0;
            padding: 3mm 2.5mm;
            border: 0;
            border-radius: 0;
            font-size: 10px;
        }

        .pos-receipt[data-paper-width="58"] {
            width: 58mm;
            max-width: 58mm;
            padding: 2mm;
            font-size: 9px;
        }

        .pos-receipt table {
            margin: 8px 0;
        }

        .pos-receipt th,
        .pos-receipt td {
            padding: 4px 1px;
        }

        .pos-receipt .receipt-header,
        .pos-receipt .receipt-meta,
        .pos-receipt .receipt-payment {
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .pos-receipt .receipt-logo {
            width: 15mm;
            height: 15mm;
        }

        .pos-receipt .receipt-header h1 { font-size: 15px; }
        .pos-receipt .receipt-total { font-size: 15px; }
        .pos-receipt .receipt-item-detail { font-size: 8px; }
        .pos-receipt .receipt-subtotal { padding: 3px 0; }

        .pos-receipt .receipt-thanks {
            padding-top: 6px;
        }

        .receipt-actions {
            display: none !important;
        }
    }
</style>

<div class="container-fluid py-3">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="receipt-actions d-flex justify-content-center align-items-center flex-wrap gap-2 my-3">
        <label class="receipt-print-settings" for="receipt-paper-width">
            Thermal width
            <select id="receipt-paper-width" aria-label="Thermal printer paper width">
                <option value="58">58 mm</option>
                <option value="80" selected>80 mm</option>
            </select>
        </label>
        <button type="button" class="btn btn-success" onclick="window.print()">
            Print Receipt
        </button>
        <a class="btn btn-outline-success" href="{{ route('admin.manual-transactions.create') }}">
            New Sale
        </a>
        <a class="btn btn-outline-secondary" href="{{ route('admin.manual-transactions.index') }}">
            Transaction History
        </a>
    </div>

    @php
        $payment = $order->payments->first();
        $isCash = strtolower((string) $order->payment_method) === 'cash';
        $cashReceived = $isCash ? (float) ($order->cash_collected_amount ?? $order->total) : null;
        $change = $isCash ? max(0, $cashReceived - (float) $order->total) : 0;
    @endphp

    <article class="pos-receipt" id="pos-receipt" data-paper-width="80">
        <header class="receipt-header">
            <img class="receipt-logo" src="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}" alt="H&amp;K Consumer Goods Store">
            <h1>H&amp;K Consumer Goods Store</h1>
            <p>Sales Receipt</p>
            <p class="receipt-store-contact">Real St., Brgy. Loyonsawang<br>Abuyog, Leyte · 09287269237</p>
        </header>

        <section class="receipt-meta">
            <div><span>Receipt No.</span><strong>{{ $order->order_number }}</strong></div>
            <div>
                <span>Date / Time</span>
                <span>{{ optional($order->completed_at ?? $order->created_at)->format('M d, Y h:i A') }}</span>
            </div>
            <div><span>Customer</span><span>{{ $order->recipient_name }}</span></div>
            @if ($order->phone && $order->phone !== 'N/A')
                <div><span>Phone</span><span>{{ $order->phone }}</span></div>
            @endif
            <div><span>Cashier</span><span>{{ $payment?->verifiedBy?->name ?? auth()->user()->name }}</span></div>
        </section>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <span class="receipt-item-name">{{ $item->product_name }}</span>
                            <small class="receipt-item-detail">
                                {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                                {{ $item->quantity_unit ?: $item->unit_type }} ×
                                ₱{{ number_format((float) $item->unit_price, 2) }} / {{ $item->quantity_unit ?: $item->unit_type }}
                            </small>
                        </td>
                        <td>₱{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="receipt-subtotal">
            <span>Subtotal</span>
            <span>₱{{ number_format((float) $order->items->sum('line_total'), 2) }}</span>
        </div>
        <div class="receipt-total">
            <div class="receipt-subtotal" style="padding:0;">
                <span>TOTAL</span>
                <span>₱{{ number_format((float) $order->total, 2) }}</span>
            </div>
        </div>

        <section class="receipt-payment">
            <h2>Payment</h2>
            <div><span>Method</span><strong>{{ strtoupper($order->payment_method) }}</strong></div>
            @if ($isCash)
                <div><span>Cash received</span><span>₱{{ number_format($cashReceived, 2) }}</span></div>
                <div><span>Change</span><strong>₱{{ number_format($change, 2) }}</strong></div>
            @elseif ($payment?->reference_number)
                <div><span>GCash reference</span><span>{{ $payment->reference_number }}</span></div>
            @endif
        </section>

        @if ($order->customer_notes)
            <p class="receipt-thanks"><small>Note: {{ $order->customer_notes }}</small></p>
        @endif

        <p class="receipt-thanks"><strong>Thank you for your purchase!</strong><br><small>Please keep this receipt for your records.</small></p>
    </article>
</div>

<script>
(() => {
    const paperWidthSelect = document.getElementById('receipt-paper-width');
    const receipt = document.getElementById('pos-receipt');

    if (!paperWidthSelect || !receipt) return;

    const storageKey = 'receipt-paper-width-{{ auth()->id() }}';
    const savedWidth = localStorage.getItem(storageKey);

    if (savedWidth === '58' || savedWidth === '80') {
        paperWidthSelect.value = savedWidth;
    }
    receipt.dataset.paperWidth = paperWidthSelect.value;

    paperWidthSelect.addEventListener('change', () => {
        receipt.dataset.paperWidth = paperWidthSelect.value;
        localStorage.setItem(storageKey, paperWidthSelect.value);
    });
})();
</script>
@endsection
