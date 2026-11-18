@extends('admin.layouts.app')

@section('title', 'Sales (Benta)')

@section('content')
<style>
    .sales-page {
        display: grid;
        gap: 18px;
        padding: 18px;
        border-radius: 16px;
        background: #e7f1e8;
        color: #23352a;
    }
    .sales-heading, .sales-panel {
        padding: 20px;
        border: 1px solid #c7ddca;
        border-radius: 14px;
        background: #f5faf5;
        box-shadow: 0 5px 18px rgb(22 53 31 / 6%);
    }
    .sales-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .sales-heading h1 { margin: 0 0 5px; font-size: 1.55rem; }
    .sales-heading p, .sales-muted { margin: 0; color: #68776c; }
    .sales-actions, .sales-form-actions { display: flex; flex-wrap: wrap; gap: 9px; }
    .sales-button {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        padding: 9px 14px;
        border: 1px solid #1d7142;
        border-radius: 8px;
        background: #1d7142;
        color: #fff;
        font: inherit;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }
    .sales-button.secondary { background: #fff; color: #1d7142; }
    .sales-button.danger { border-color: #a33a32; background: #a33a32; }
    .sales-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .sales-metric {
        min-width: 0;
        padding: 17px;
        border: 1px solid #c4dbc8;
        border-radius: 12px;
        background: #dcecdf;
    }
    .sales-metric span { display: block; color: #637368; font-size: .85rem; }
    .sales-metric strong { display: block; margin-top: 6px; color: #165c36; font-size: 1.45rem; overflow-wrap: anywhere; }
    .sales-layout { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(280px, .7fr); gap: 16px; }
    .sales-panel h2 { margin: 0 0 14px; font-size: 1.15rem; }
    .sales-form { display: grid; gap: 12px; }
    .sales-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .sales-fields label { display: grid; gap: 6px; color: #35473a; font-size: .88rem; font-weight: 700; }
    .sales-fields input, .sales-fields select, .sales-fields textarea {
        width: 100%;
        min-height: 42px;
        padding: 9px 10px;
        border: 1px solid #ccd8ce;
        border-radius: 7px;
        background: #fff;
        color: #243126;
        font: inherit;
    }
    .sales-fields textarea { min-height: 75px; resize: vertical; }
    .sales-wide { grid-column: 1 / -1; }
    .sales-shift-state {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 20px;
        background: #e8f4eb;
        color: #17613a;
        font-size: .82rem;
        font-weight: 700;
    }
    .sales-table-wrap { overflow-x: auto; }
    .sales-table { width: 100%; border-collapse: collapse; min-width: 680px; }
    .sales-table th, .sales-table td { padding: 11px 10px; border-bottom: 1px solid #e8eee8; text-align: left; vertical-align: top; }
    .sales-table th { color: #627166; font-size: .79rem; text-transform: uppercase; }
    .sales-table td { font-size: .88rem; }
    .sales-table a { color: #17613a; font-weight: 700; }
    .sales-links { display: flex; flex-wrap: wrap; gap: 9px; }
    .sales-links a { color: #17613a; font-weight: 700; }
    .sales-alert { padding: 11px 13px; border-radius: 8px; background: #e8f4eb; color: #155d36; }
    .sales-alert.error { background: #fff0ef; color: #922f29; }
    .sales-page .pagination svg { width: 20px; height: 20px; }
    .sales-page .pagination .page-link { color: #17613a; }
    .sales-page .pagination .page-item.active .page-link {
        border-color: #1d7142;
        background: #1d7142;
        color: #fff;
    }
    :root[data-theme="dark"] .sales-page { background: #0d1323; color: #ecf1ff; }
    :root[data-theme="dark"] .sales-page .sales-heading,
    :root[data-theme="dark"] .sales-page .sales-panel { border-color: #293149; background: #131b2e; }
    :root[data-theme="dark"] .sales-page .sales-metric { border-color: #293149; background: #1a2439; }
    :root[data-theme="dark"] .sales-page .sales-heading p,
    :root[data-theme="dark"] .sales-page .sales-muted,
    :root[data-theme="dark"] .sales-page .sales-metric span,
    :root[data-theme="dark"] .sales-page .sales-table th { color: #9aa5bd; }
    :root[data-theme="dark"] .sales-page .sales-fields label { color: #d3dbed; }
    :root[data-theme="dark"] .sales-page .sales-fields input,
    :root[data-theme="dark"] .sales-page .sales-fields select,
    :root[data-theme="dark"] .sales-page .sales-fields textarea {
        border-color: #3a465e;
        background: #11192a;
        color: #ecf1ff;
    }
    :root[data-theme="dark"] .sales-page .sales-shift-state {
        background: #1a2439;
        color: #b7e5c1;
    }
    :root[data-theme="dark"] .sales-page .sales-table th,
    :root[data-theme="dark"] .sales-page .sales-table td { border-color: #293149; }
    :root[data-theme="dark"] .sales-page .sales-table a,
    :root[data-theme="dark"] .sales-page .sales-links a { color: #b7e5c1; }
    :root[data-theme="dark"] .sales-page .sales-alert { background: #1a2439; color: #b7e5c1; }
    :root[data-theme="dark"] .sales-page .sales-alert.error { background: #3a202b; color: #ffb4b4; }
    :root[data-theme="dark"] .sales-page .sales-metric strong { color: #a7dfb4; }
    :root[data-theme="dark"] .sales-page .pagination .page-link { border-color: #293149; background: #131b2e; color: #b7c8f4; }
    :root[data-theme="dark"] .sales-page .pagination .page-item.active .page-link { background: #1d7142; color: #fff; }
    @media (max-width: 820px) {
        .sales-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sales-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 520px) {
        .sales-heading, .sales-panel { padding: 15px; }
        .sales-grid, .sales-fields { grid-template-columns: 1fr; }
        .sales-wide { grid-column: auto; }
        .sales-actions, .sales-form-actions { display: grid; }
        .sales-button { width: 100%; }
    }
</style>

<main class="sales-page">
    <header class="sales-heading">
        <div>
            <h1>Sales (Benta)</h1>
            <p>Point of sale, cash drawer, receipts, and sales records in one place.</p>
        </div>
        <nav class="sales-actions" aria-label="Sales actions">
            <a class="sales-button" href="{{ route('admin.manual-transactions.create') }}">+ New Manual Transaction</a>
            <a class="sales-button secondary" href="{{ route('admin.manual-transactions.index') }}">Invoices &amp; Receipts</a>
        </nav>
    </header>

    @if (session('success'))
        <div class="sales-alert" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="sales-alert error" role="alert">
            <strong>Action could not be completed.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="sales-grid" aria-label="Sales summary">
        <article class="sales-metric"><span>Today's Sales</span><strong>₱{{ number_format($todaySales, 2) }}</strong></article>
        <article class="sales-metric"><span>Today's Profit</span><strong>₱{{ number_format($todayProfit, 2) }}</strong></article>
        <article class="sales-metric"><span>Transactions Today</span><strong>{{ number_format($todayOrders->count()) }}</strong></article>
        <article class="sales-metric"><span>Total Sales (All Time)</span><strong>₱{{ number_format($allTimeSales, 2) }}</strong></article>
    </section>

    <section class="sales-layout">
        <article class="sales-panel">
            <h2>Cash Drawer</h2>
            @if ($shift)
                <p class="sales-muted">
                    <span class="sales-shift-state">SHIFT OPEN</span>
                    Opened by {{ $shift->opener->name }} · {{ $shift->opened_at->format('M d, Y h:i A') }}
                </p>
                <div class="sales-grid" style="margin:14px 0;">
                    <div class="sales-metric"><span>Opening Cash</span><strong>₱{{ number_format((float) $shift->opening_cash, 2) }}</strong></div>
                    <div class="sales-metric"><span>Cash Sales</span><strong>₱{{ number_format($cashSales, 2) }}</strong></div>
                    <div class="sales-metric"><span>Cash In / Out</span><strong>₱{{ number_format($cashIn - $cashOut, 2) }}</strong></div>
                    <div class="sales-metric"><span>Expected Cash</span><strong>₱{{ number_format($expectedCash, 2) }}</strong></div>
                </div>

                <div class="sales-layout">
                    <form class="sales-form" method="POST" action="{{ route('admin.sales.shifts.cash-movements.store') }}">
                        @csrf
                        <h2>Cash In / Cash Out</h2>
                        <div class="sales-fields">
                            <label>Movement
                                <select name="movement_type" required>
                                    <option value="cash_in">Cash In</option>
                                    <option value="cash_out">Cash Out</option>
                                </select>
                            </label>
                            <label>Amount (₱)
                                <input name="amount" type="number" min="0.01" max="100000000" step="0.01" required>
                            </label>
                            <label class="sales-wide">Reason / Note
                                <input name="note" type="text" maxlength="255" placeholder="Optional note">
                            </label>
                        </div>
                        <button class="sales-button" type="submit">Record Movement</button>
                    </form>

                    <form class="sales-form" method="POST" action="{{ route('admin.sales.shifts.close') }}">
                        @csrf
                        <h2>End of Shift</h2>
                        <p class="sales-muted">Count the cash in the drawer. Expected cash: <strong>₱{{ number_format($expectedCash, 2) }}</strong></p>
                        <label class="sales-fields"><span>Actual Closing Cash (₱)</span>
                            <input name="closing_cash" type="number" min="0" max="100000000" step="0.01" required>
                        </label>
                        <button class="sales-button danger" type="submit">Close Shift</button>
                    </form>
                </div>

                @if ($shift->movements->isNotEmpty())
                    <h2 style="margin-top:20px;">Cash Drawer Activity</h2>
                    <div class="sales-table-wrap">
                        <table class="sales-table">
                            <thead><tr><th>Time</th><th>Type</th><th>Amount</th><th>Note</th><th>Recorded by</th></tr></thead>
                            <tbody>
                                @foreach ($shift->movements->sortByDesc('created_at') as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('h:i A') }}</td>
                                        <td>{{ str_replace('_', ' ', ucfirst($movement->movement_type)) }}</td>
                                        <td>₱{{ number_format((float) $movement->amount, 2) }}</td>
                                        <td>{{ $movement->note ?: '—' }}</td>
                                        <td>{{ $movement->creator->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <p class="sales-muted">Open a shift before recording a POS sale. Each user manages their own drawer shift.</p>
                <form class="sales-form" method="POST" action="{{ route('admin.sales.shifts.open') }}">
                    @csrf
                    <div class="sales-fields">
                        <label>Opening Cash (₱)
                            <input name="opening_cash" type="number" min="0" max="100000000" step="0.01" value="{{ old('opening_cash', 0) }}" required>
                        </label>
                    </div>
                    <button class="sales-button" type="submit">Open Cash Drawer</button>
                </form>
            @endif
        </article>

        <article class="sales-panel">
            <h2>Connect Existing Store Tools</h2>
            <p class="sales-muted">These existing records stay linked to the same store inventory and reports.</p>
            <nav class="sales-links" aria-label="Store tools">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}">{{ $link['label'] }}</a>
                @endforeach
            </nav>
            <h2 style="margin-top:22px;">Recent Shifts</h2>
            @forelse ($recentShifts as $recentShift)
                <p class="sales-muted">
                    {{ $recentShift->opener->name }} · {{ $recentShift->opened_at->format('M d, h:i A') }}
                    · {{ $recentShift->status === 'open' ? 'Open' : 'Closed' }}
                    @if ($recentShift->status === 'closed')
                        · Variance ₱{{ number_format((float) $recentShift->cash_variance, 2) }}
                    @endif
                </p>
            @empty
                <p class="sales-muted">No cash drawer shifts recorded yet.</p>
            @endforelse
        </article>
    </section>

    <section class="sales-panel">
        <div class="sales-heading" style="padding:0;border:0;box-shadow:none;">
            <div><h2>Recent POS Sales</h2><p>Completed sales with their associated inventory records.</p></div>
            <span class="sales-muted">{{ $transactions->total() }} transactions</span>
        </div>
        <div class="sales-table-wrap">
            <table class="sales-table">
                <thead><tr><th>Sale</th><th>Date</th><th>Customer</th><th>Items</th><th>Payment</th><th>Total</th><th>Receipt</th></tr></thead>
                <tbody>
                    @forelse ($transactions as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->completed_at?->format('M d, Y h:i A') ?: '—' }}</td>
                            <td>{{ $order->recipient_name }}</td>
                            <td>
                                @foreach ($order->items as $item)
                                    <div>{{ $item->product_name }} · {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} {{ $item->quantity_unit ?: $item->unit_type }}</div>
                                @endforeach
                            </td>
                            <td>{{ strtoupper($order->payment_method) }}</td>
                            <td>₱{{ number_format((float) $order->total, 2) }}</td>
                            <td><a href="{{ route('admin.manual-transactions.receipt', $order) }}">View / Print</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No POS sales have been recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:14px;">{{ $transactions->links('pagination::bootstrap-5') }}</div>
    </section>
</main>
@endsection
