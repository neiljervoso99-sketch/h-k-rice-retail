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
        padding-bottom: 16px;
        border-bottom: 1px dashed #8a978c;
    }

    .pos-receipt .receipt-header h1 {
        margin-bottom: 5px;
        font-size: 20px;
    }

    .pos-receipt .receipt-meta,
    .pos-receipt .receipt-payment {
        padding: 14px 0;
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
        margin: 14px 0;
        border-collapse: collapse;
    }

    .pos-receipt th,
    .pos-receipt td {
        padding: 7px 0;
        text-align: left;
        vertical-align: top;
    }

    .pos-receipt th:last-child,
    .pos-receipt td:last-child {
        text-align: right;
        white-space: nowrap;
    }

    .pos-receipt .receipt-total {
        padding: 14px 0;
        border-top: 1px dashed #8a978c;
        border-bottom: 1px dashed #8a978c;
        font-size: 20px;
        font-weight: 700;
    }

    .pos-receipt .receipt-thanks {
        padding-top: 16px;
        text-align: center;
    }

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
            width: 72mm;
            max-width: 72mm;
            margin: 0;
            padding: 3mm;
            border: 0;
            border-radius: 0;
            font-size: 11px;
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

    <div class="receipt-actions d-flex justify-content-center gap-2 my-3">
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

    <article class="pos-receipt">
        <header class="receipt-header">
            <h1>H&amp;K Consumer Goods Store</h1>
            <p>Sales Receipt</p>
        </header>

        <section class="receipt-meta">
            <div><span>Transaction</span><strong>{{ $order->order_number }}</strong></div>
            <div>
                <span>Date</span>
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
                    <th>Qty × Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}<br><small>{{ $item->unit_type }}</small></td>
                        <td>
                            {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                            × ₱{{ number_format((float) $item->unit_price, 2) }}
                        </td>
                        <td>₱{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="receipt-total">
            Total: ₱{{ number_format((float) $order->total, 2) }}
        </div>

        <section class="receipt-payment">
            <div><span>Payment</span><strong>{{ strtoupper($order->payment_method) }}</strong></div>
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

        <p class="receipt-thanks">Thank you for shopping with us!</p>
    </article>
</div>
@endsection
