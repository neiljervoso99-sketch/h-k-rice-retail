<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales &amp; Profit Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #26352b; font-size: 10px; }
        h1 { margin: 0 0 5px; color: #245b38; font-size: 20px; }
        h2 { margin: 18px 0 8px; color: #245b38; font-size: 13px; }
        p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px; border: 1px solid #dfe6dc; text-align: left; }
        th { background: #edf4ee; }
        .numeric { text-align: right; }
        .summary td:first-child { width: 70%; }
        .muted { color: #66756a; }
    </style>
</head>
<body>
    <h1>Sales &amp; Profit Report</h1>
    <p>Report period: {{ $startDate }} to {{ $endDate }}</p>
    <p class="muted">Completed transactions and recorded expenses within the selected dates.</p>

    <h2>Summary</h2>
    <table class="summary">
        <tbody>
            <tr><td>Total sales</td><td class="numeric">PHP {{ number_format($salesTotal, 2) }}</td></tr>
            <tr><td>Completed orders</td><td class="numeric">{{ number_format($completedOrderCount) }}</td></tr>
            <tr><td>Recorded item sales</td><td class="numeric">PHP {{ number_format($recordedItemSales, 2) }}</td></tr>
            <tr><td>Recorded item cost</td><td class="numeric">PHP {{ number_format($recordedItemCost, 2) }}</td></tr>
            <tr><td>Recorded gross profit</td><td class="numeric">PHP {{ number_format($grossProfit, 2) }}</td></tr>
            <tr><td>Recorded expenses</td><td class="numeric">PHP {{ number_format($expensesTotal, 2) }}</td></tr>
            <tr><td>Estimated net profit</td><td class="numeric">PHP {{ number_format($netProfit, 2) }}</td></tr>
            <tr><td>Items without recorded cost</td><td class="numeric">{{ number_format($itemsWithoutCostCount) }}</td></tr>
        </tbody>
    </table>

    <h2>Daily Sales Breakdown</h2>
    <table>
        <thead><tr><th>Date</th><th class="numeric">Completed orders</th><th class="numeric">Total sales</th></tr></thead>
        <tbody>
            @forelse ($dailySales as $day)
                <tr>
                    <td>{{ $day->sales_date }}</td>
                    <td class="numeric">{{ number_format((int) $day->order_count) }}</td>
                    <td class="numeric">PHP {{ number_format((float) $day->sales_total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No completed sales for this date range.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Product Profit</h2>
    <table>
        <thead><tr><th>Product</th><th>Brand</th><th class="numeric">Quantity</th><th class="numeric">Sales</th><th class="numeric">Cost</th><th class="numeric">Profit</th></tr></thead>
        <tbody>
            @forelse ($varieties as $variety)
                <tr>
                    <td>{{ $variety->product_name }}</td>
                    <td>{{ $variety->brand ?: '—' }}</td>
                    <td class="numeric">{{ number_format((float) $variety->quantity, 2) }}</td>
                    <td class="numeric">PHP {{ number_format((float) $variety->sales, 2) }}</td>
                    <td class="numeric">PHP {{ number_format((float) $variety->cost, 2) }}</td>
                    <td class="numeric">PHP {{ number_format((float) $variety->profit, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No completed items with recorded cost for this date range.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Completed Transactions</h2>
    <table>
        <thead><tr><th>Order</th><th>Date completed</th><th>Customer</th><th>Payment method</th><th>GCash reference</th><th>Payment status</th><th class="numeric">Total</th></tr></thead>
        <tbody>
            @forelse ($transactions as $transaction)
                @php
                    $payment = $transaction->payments->first(
                        fn ($payment) => strtolower(trim((string) $payment->method)) === 'gcash'
                    );
                @endphp
                <tr>
                    <td>{{ $transaction->order_number }}</td>
                    <td>{{ $transaction->completed_at?->toDateTimeString() }}</td>
                    <td>{{ $transaction->recipient_name ?: 'N/A' }}</td>
                    <td>{{ strtoupper($transaction->payment_method ?: 'N/A') }}</td>
                    <td>{{ $payment?->reference_number ?: '—' }}</td>
                    <td>{{ ucfirst($transaction->payment_status ?: 'unknown') }}</td>
                    <td class="numeric">PHP {{ number_format((float) $transaction->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No completed transactions for this date range.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
