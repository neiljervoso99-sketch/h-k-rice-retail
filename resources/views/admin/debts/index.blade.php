@extends('admin.layouts.app')

@section('title', 'Utang')

@section('content')
<style>
    .utang-drawer {
        position: fixed;
        inset: 0 0 0 auto;
        width: min(960px, 100%);
        max-width: none;
        height: 100dvh;
        max-height: 100dvh;
        margin: 0;
        padding: 0;
        overflow: hidden;
        border: 0;
        background: var(--page-bg);
        color: var(--text);
        box-shadow: -16px 0 48px rgb(0 0 0 / 24%);
    }
    .utang-drawer[open] { display: flex; flex-direction: column; }
    .utang-drawer::backdrop { background: rgb(0 0 0 / 48%); }
    .utang-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 62px;
        padding: 10px 16px;
        border-bottom: 1px solid var(--border);
        background: var(--page-bg);
    }
    .utang-drawer-header h2 { margin: 0; font-size: 17px; }
    .utang-drawer-tabs {
        display: flex;
        gap: 8px;
        padding: 10px 16px;
        border-bottom: 1px solid var(--border);
    }
    .utang-drawer-tabs button[aria-selected="true"] { background: var(--green); color: #fff; }
    .utang-drawer-content { flex: 1; min-height: 0; overflow: auto; }
    .utang-drawer-product { height: 100%; }
    .utang-drawer-product iframe { display: block; width: 100%; height: 100%; min-height: 0; border: 0; }
    .utang-cash-pane { max-width: 700px; margin: 0 auto; padding: 20px; }
    .utang-cash-pane[hidden], .utang-drawer-product[hidden] { display: none !important; }
    .utang-cash-pane form { display: grid; gap: 14px; }
    .utang-cash-pane label { display: grid; gap: 6px; font-weight: 700; }
    .utang-cash-pane input, .utang-cash-pane textarea {
        width: 100%; min-height: 44px; padding: 10px 12px;
        border: 1px solid var(--border); border-radius: 6px;
        background: var(--page-bg); color: var(--text); font: inherit;
    }
    .utang-cash-pane textarea { min-height: 90px; resize: vertical; }
    .utang-drawer-close { min-width: 40px; padding: 7px; }
    .utang-status {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        margin-top: 5px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }
    .utang-status.unpaid { background: #fff0ef; color: #a52b24; }
    .utang-status.partial { background: #fff5dc; color: #815d12; }
    .utang-status.paid { background: #e7f5e9; color: #24633a; }
</style>
<div class="page-heading">
    <h1>Utang</h1>
    <p>Track customer debts, payment history, and remaining balances.</p>
    <button class="button" id="open-utang-drawer" type="button">Record Utang</button>
</div>

@if (session('success'))
    <div class="notice success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="notice error">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="grid grid-3">
    <section class="panel">
        <h2>Total Utang</h2>
        <strong>₱{{ number_format($totalOwed, 2) }}</strong>
    </section>
    <section class="panel">
        <h2>Total Paid</h2>
        <strong>₱{{ number_format($totalPaid, 2) }}</strong>
    </section>
    <section class="panel">
        <h2>Outstanding Balance</h2>
        <strong>₱{{ number_format($totalBalance, 2) }}</strong>
    </section>
</div>

<dialog class="utang-drawer" id="utang-drawer" aria-labelledby="utang-drawer-title">
    <div class="utang-drawer-header">
        <h2 id="utang-drawer-title">Record Utang</h2>
        <button class="button utang-drawer-close" id="close-utang-drawer" type="button" aria-label="Close Utang drawer">&times;</button>
    </div>
    <div class="utang-drawer-tabs" role="tablist" aria-label="Utang type">
        <button class="button" type="button" role="tab" id="product-debt-tab" aria-selected="true" aria-controls="product-debt-pane">Product Utang</button>
        <button class="button" type="button" role="tab" id="cash-loan-tab" aria-selected="false" aria-controls="cash-loan-pane">Cash Loan</button>
    </div>
    <div class="utang-drawer-content">
        <div class="utang-drawer-product" id="product-debt-pane" role="tabpanel" aria-labelledby="product-debt-tab">
            <iframe id="product-debt-frame" name="product-debt-frame" title="Record product debt"></iframe>
        </div>
        <section class="utang-cash-pane" id="cash-loan-pane" role="tabpanel" aria-labelledby="cash-loan-tab" hidden>
            <h3>Cash Loan</h3>
            <p class="muted">The loan will be recorded as cash out. Inventory will not change.</p>
            @unless ($hasOpenShift)
                <div class="notice error">Open a cash drawer shift before recording a cash loan.</div>
                <a class="button" href="{{ route('admin.sales.index') }}">Open Cash Drawer</a>
            @endunless
            <form method="POST" action="{{ route('admin.debts.cash-loans.store') }}" target="product-debt-frame" id="cash-loan-form">
                @csrf
                <label>
                    Customer Name
                    <input type="text" name="customer_name" maxlength="255" required>
                </label>
                <label>
                    Phone Number
                    <input type="text" name="customer_phone" maxlength="30" placeholder="Optional">
                </label>
                <label>
                    Cash Amount (PHP)
                    <input type="number" name="amount" min="0.01" max="100000000" step="0.01" required>
                </label>
                <label>
                    Notes
                    <textarea name="notes" maxlength="1000" placeholder="Optional loan details"></textarea>
                </label>
                <button class="button" type="submit" @disabled(!$hasOpenShift)>Record Cash Loan</button>
            </form>
        </section>
    </div>
</dialog>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Customer / Date &amp; Time</th>
                <th>Products</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Payment History</th>
                <th>Record Payment</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($debts as $debt)
                @php
                    $paid = round((float) $debt->payments->sum('amount'), 2);
                    $balance = max(0, round((float) $debt->total - $paid, 2));
                    $debtStatus = $balance <= 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid');
                @endphp
                <tr>
                    <td>
                        <strong>{{ $debt->recipient_name }}</strong>
                        <div>{{ $debt->phone ?: 'No phone recorded' }}</div>
                        <small>{{ $debt->created_at?->format('M d, Y h:i A') }}</small>
                        <div><small>{{ $debt->order_number }}</small></div>
                    </td>
                    <td>
                        @if ($debt->items->isNotEmpty())
                            @foreach ($debt->items as $item)
                                <div>{{ $item->product_name }} · {{ number_format((float) $item->quantity, 2) }} {{ $item->quantity_unit ?: $item->unit_type }}</div>
                            @endforeach
                        @else
                            <strong>Cash Loan</strong>
                            @if ($debt->customer_notes)<div>{{ $debt->customer_notes }}</div>@endif
                        @endif
                    </td>
                    <td>₱{{ number_format((float) $debt->total, 2) }}</td>
                    <td>₱{{ number_format($paid, 2) }}</td>
                    <td>
                        <strong>₱{{ number_format($balance, 2) }}</strong>
                        <div><span class="utang-status {{ strtolower($debtStatus) }}">{{ $debtStatus }}</span></div>
                    </td>
                    <td>
                        @forelse ($debt->payments as $payment)
                            <div>
                                ₱{{ number_format((float) $payment->amount, 2) }} · {{ ucfirst($payment->method) }}
                                <small>{{ $payment->verified_at?->format('M d, Y h:i A') }}</small>
                            </div>
                        @empty
                            —
                        @endforelse
                    </td>
                    <td>
                        @if ($balance > 0)
                            <form method="POST" action="{{ route('admin.debts.payments.store', $debt) }}">
                                @csrf
                                <input type="number" name="amount" min="0.01" max="{{ number_format($balance, 2, '.', '') }}" step="0.01" placeholder="Amount" required>
                                <select name="method" required>
                                    <option value="cash">Cash</option>
                                    <option value="gcash">GCash</option>
                                </select>
                                <input type="text" name="reference_number" maxlength="100" placeholder="GCash reference if applicable">
                                <button class="button" type="submit">Save Payment</button>
                            </form>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No customer debts have been recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $debts->links() }}
<script>
(() => {
    const drawer = document.getElementById('utang-drawer');
    const frame = document.getElementById('product-debt-frame');
    const productTab = document.getElementById('product-debt-tab');
    const cashTab = document.getElementById('cash-loan-tab');
    const productPane = document.getElementById('product-debt-pane');
    const cashPane = document.getElementById('cash-loan-pane');

    if (!drawer || !frame) return;

    const productFormUrl = @json(route('admin.manual-transactions.create').'?payment_method=utang&drawer=1');

    function selectTab(tab) {
        const showProduct = tab === 'product';
        productTab.setAttribute('aria-selected', String(showProduct));
        cashTab.setAttribute('aria-selected', String(!showProduct));
        productPane.hidden = !showProduct;
        cashPane.hidden = showProduct;

        if (showProduct && (!frame.src || frame.src === 'about:blank')) {
            frame.src = productFormUrl;
        }
    }

    document.getElementById('open-utang-drawer')?.addEventListener('click', () => {
        selectTab('product');
        drawer.showModal();
    });
    document.getElementById('close-utang-drawer')?.addEventListener('click', () => drawer.close());
    productTab.addEventListener('click', () => selectTab('product'));
    cashTab.addEventListener('click', () => selectTab('cash'));
    drawer.addEventListener('click', event => {
        if (event.target === drawer) drawer.close();
    });

    frame.addEventListener('load', () => {
        try {
            const loaded = new URL(frame.contentWindow.location.href);
            const saved = loaded.searchParams.get('saved');

            if (loaded.pathname === @json(route('admin.debts.index')) && saved) {
                drawer.close();
                window.location.reload();
            }
        } catch (error) {
            return;
        }
    });
})();
</script>
@endsection