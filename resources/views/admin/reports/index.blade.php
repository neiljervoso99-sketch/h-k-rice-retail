@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
<style>
    .reports-page {
        color: #26352b;
    }

    .reports-header {
        margin-bottom: 22px;
    }

    .reports-header h1 {
        margin: 0;
        color: #245b38;
        font-size: 27px;
        font-weight: 700;
    }

    .reports-header p {
        margin: 5px 0 0;
        color: #718078;
        font-size: 14px;
    }

    .report-panel,
    .summary-card {
        min-width: 0;
        background: #fff;
        border: 1px solid #e2eae4;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(24, 65, 39, 0.05);
    }

    .report-panel {
        padding: 20px;
        margin-bottom: 20px;
    }

    .report-panel h2 {
        margin: 0 0 14px;
        color: #245b38;
        font-size: 18px;
        font-weight: 700;
    }

    .report-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        gap: 12px;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-field label {
        color: #526458;
        font-size: 13px;
        font-weight: 600;
    }

    .filter-field select,
    .filter-field input {
        min-width: 160px;
        padding: 10px 12px;
        border: 1px solid #cedbd1;
        border-radius: 8px;
        background: #fff;
        color: #26352b;
    }

    .filter-button {
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        background: #245b38;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }

    .filter-button:hover {
        background: #19472b;
    }

    .report-tools {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .report-download,
    .report-print {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 9px 14px;
        border: 0;
        border-radius: 8px;
        background: #245b38;
        color: #fff;
        font: inherit;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .report-download:hover,
    .report-print:hover {
        background: #19472b;
        color: #fff;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .summary-card {
        padding: 18px;
    }

    .summary-label {
        margin-bottom: 8px;
        color: #718078;
        font-size: 13px;
        font-weight: 600;
    }

    .summary-value {
        color: #245b38;
        font-size: 23px;
        line-height: 1.35;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .summary-note {
        margin-top: 7px;
        color: #839087;
        font-size: 12px;
    }

    .notice {
        padding: 13px 16px;
        margin-bottom: 20px;
        border: 1px solid #f1d99a;
        border-radius: 9px;
        background: #fff9e8;
        color: #795b12;
        font-size: 14px;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 20px;
        align-items: stretch;
    }

    .chart-panel {
        min-width: 0;
        margin-bottom: 0;
        padding: 18px;
    }

    .chart-panel h2 {
        margin-bottom: 5px;
        font-size: 17px;
    }

    .chart-description {
        margin: 0 0 12px;
        color: #7b897f;
        font-size: 12px;
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: 250px;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .report-table th,
    .report-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #e8eee9;
        text-align: left;
        white-space: nowrap;
    }

    .report-table th {
        background: #f3f8f4;
        color: #42614b;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .report-table tbody tr:hover {
        background: #f8fbf8;
    }

    .report-table .numeric {
        text-align: right;
    }

    .empty-state {
        padding: 25px;
        text-align: center !important;
        color: #87938a;
    }

    :root[data-theme="dark"] .reports-page {
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .reports-page .reports-header h1,
    :root[data-theme="dark"] .reports-page .report-panel h2 {
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .reports-page .reports-header p,
    :root[data-theme="dark"] .reports-page .summary-note,
    :root[data-theme="dark"] .reports-page .chart-description,
    :root[data-theme="dark"] .reports-page .summary-label {
        color: #aab6cb;
    }

    :root[data-theme="dark"] .reports-page .report-panel,
    :root[data-theme="dark"] .reports-page .summary-card {
        border-color: #293149;
        background: #131b2e;
        color: #ecf1ff;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .14);
    }

    :root[data-theme="dark"] .reports-page .summary-value {
        color: #c9ead0;
    }

    :root[data-theme="dark"] .reports-page .filter-field label {
        color: #d3dbed;
    }

    :root[data-theme="dark"] .reports-page .filter-field select,
    :root[data-theme="dark"] .reports-page .filter-field input {
        border-color: #3a465e;
        background: #11192a;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .reports-page .notice {
        border-color: #66512a;
        background: #332a18;
        color: #f5d894;
    }

    :root[data-theme="dark"] .reports-page .report-table th {
        border-bottom-color: #293149;
        background: #1a2439;
        color: #d3dbed;
    }

    :root[data-theme="dark"] .reports-page .report-table td {
        border-bottom-color: #293149;
        color: #d3dbed;
    }

    :root[data-theme="dark"] .reports-page .report-table tbody tr:hover {
        background: #1a2439;
    }

    :root[data-theme="dark"] .reports-page .empty-state {
        color: #aab6cb;
    }

    @media (max-width: 900px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .charts-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 520px) {
        .reports-header h1 {
            font-size: 23px;
        }

        .summary-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .summary-value {
            font-size: 21px;
        }

        .report-panel {
            padding: 14px;
        }

        .report-filters,
        .filter-field,
        .filter-field select,
        .filter-field input,
        .filter-button {
            width: 100%;
        }

        .chart-container {
            height: 230px;
        }
    }

    @media print {
        @page {
            size: landscape;
            margin: 12mm;
        }

        body {
            background: #fff !important;
            color: #17231a !important;
        }

        .sidebar,
        .admin-header,
        .report-filters,
        .report-tools,
        .notification-center,
        .page-heading,
        .admin-main > .notice {
            display: none !important;
        }

        .admin-main {
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .reports-page,
        .reports-page .report-panel,
        .reports-page .summary-card {
            color: #17231a !important;
            border-color: #bcc7be !important;
            background: #fff !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .reports-page .reports-header,
        .reports-page .reports-header h1,
        .reports-page .report-panel h2,
        .reports-page .summary-value,
        .reports-page .report-table td,
        .reports-page .report-table th {
            color: #17231a !important;
        }

        .reports-page .charts-grid {
            display: none !important;
        }

        .reports-page .table-wrapper {
            overflow: visible !important;
        }

        .reports-page .report-table {
            font-size: 9pt;
        }

        .reports-page .report-table th,
        .reports-page .report-table td {
            padding: 5px 7px;
            white-space: normal;
        }
    }
</style>

<div class="reports-page">

    <div class="reports-header">
        <h1>Sales &amp; Profit Reports</h1>
        <p>Monitor sales performance, profit, expenses, and payment methods.</p>
        <p class="report-date-range">Showing {{ $startDate }} through {{ $endDate }}</p>
    </div>

    <section class="report-panel">
        <h2>Report Filters</h2>

        <form action="{{ route('admin.reports.index') }}"
              method="GET"
              class="report-filters">

            <div class="filter-field">
                <label for="period">Report Period</label>
                <select name="period" id="period">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom date range</option>
                </select>
            </div>

            <div class="filter-field">
                <label for="date">Reference Date</label>
                <input type="date" name="date" id="date" value="{{ $selectedDate }}">
            </div>

            <div class="filter-field">
                <label for="start_date">Custom start date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}">
            </div>

            <div class="filter-field">
                <label for="end_date">Custom end date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}">
            </div>

            <button type="submit" class="filter-button">Generate Report</button>
        </form>

        <div class="report-tools">
            <a class="report-download"
               href="{{ route('admin.reports.export', request()->only(['period', 'date', 'start_date', 'end_date'])) }}">
                Download CSV
            </a>
            <a class="report-download"
               href="{{ route('admin.reports.export.excel', request()->only(['period', 'date', 'start_date', 'end_date'])) }}">
                Download Excel
            </a>
            <a class="report-download"
               href="{{ route('admin.reports.export.pdf', request()->only(['period', 'date', 'start_date', 'end_date'])) }}">
                Download PDF
            </a>
            <button class="report-print" type="button" onclick="window.print()">
                Print Report
            </button>
        </div>
    </section>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Sales</div>
            <div class="summary-value">₱{{ number_format((float) $salesTotal, 2) }}</div>
            <div class="summary-note">Completed orders in selected period</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Completed Orders</div>
            <div class="summary-value">{{ number_format((int) $completedOrderCount) }}</div>
            <div class="summary-note">Successfully completed transactions</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Recorded Item Sales</div>
            <div class="summary-value">₱{{ number_format((float) $recordedItemSales, 2) }}</div>
            <div class="summary-note">Items with recorded unit cost</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Recorded Item Cost</div>
            <div class="summary-value">₱{{ number_format((float) $recordedItemCost, 2) }}</div>
            <div class="summary-note">Cost of items with recorded unit cost</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Gross Profit</div>
            <div class="summary-value">₱{{ number_format((float) $grossProfit, 2) }}</div>
            <div class="summary-note">Recorded item sales minus recorded item cost</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Total Expenses</div>
            <div class="summary-value">₱{{ number_format((float) $expensesTotal, 2) }}</div>
            <div class="summary-note">Recorded expenses in selected period</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Estimated Net Profit</div>
            <div class="summary-value">₱{{ number_format((float) $netProfit, 2) }}</div>
            <div class="summary-note">Recorded gross profit minus recorded expenses</div>
        </div>
    </div>

    @if (($itemsWithoutCostCount ?? 0) > 0)
        <div class="notice">
            <strong>Notice:</strong>
            {{ number_format((int) $itemsWithoutCostCount) }}
            order item record(s) do not have a recorded unit cost.
            These items are excluded from recorded item sales, recorded item cost,
            and gross profit calculations.
        </div>
    @endif

    <div class="charts-grid">

        <section class="report-panel chart-panel">
            <h2>Sales Analytics</h2>
            <p class="chart-description">Daily sales trend for the selected report period.</p>
            <div class="chart-container">
                <canvas id="salesAnalyticsChart"></canvas>
            </div>
        </section>

        <section class="report-panel chart-panel">
            <h2>Payment Methods</h2>
            <p class="chart-description">Cash and GCash sales distribution.</p>
            <div class="chart-container">
                <canvas id="paymentMethodsChart"></canvas>
            </div>
        </section>

    </div>

    <section class="report-panel">
        <h2>Daily Sales Breakdown</h2>

        <div class="table-wrapper">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="numeric">Completed Orders</th>
                        <th class="numeric">Total Sales</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($dailySales as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day->sales_date)->format('M d, Y') }}</td>
                            <td class="numeric">{{ number_format((int) $day->order_count) }}</td>
                            <td class="numeric">₱{{ number_format((float) $day->sales_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">
                                No completed sales found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="report-panel">
        <h2>Completed Transaction Details</h2>

        <div class="table-wrapper">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Date Completed</th>
                        <th>Customer</th>
                        <th>Payment Method</th>
                        <th>GCash Reference Number</th>
                        <th class="numeric">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactionDetails as $transaction)
                        <tr>
                            <td>{{ $transaction->order_number }}</td>
                            <td>{{ $transaction->completed_at }}</td>
                            <td>{{ $transaction->recipient_name ?: 'N/A' }}</td>
                            <td>{{ strtoupper($transaction->payment_method ?: 'N/A') }}</td>
                            <td>
                                @if (strtolower(trim((string) $transaction->payment_method)) === 'gcash')
                                    @php
                                        $payment = $transaction->payments
                                            ->firstWhere('method', 'gcash');
                                    @endphp
                                    {{ $payment?->reference_number ?: 'Not recorded' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="numeric">
                                ₱{{ number_format((float) $transaction->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                No completed transactions found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="report-panel">
        <h2>Profit per Rice Variety</h2>
        <p class="chart-description">
            Estimated profit uses completed order items with recorded unit cost only.
        </p>

        <div class="table-wrapper">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Rice Variety / Product</th>
                        <th>Brand</th>
                        <th class="numeric">Quantity</th>
                        <th class="numeric">Recorded Sales</th>
                        <th class="numeric">Recorded Cost</th>
                        <th class="numeric">Estimated Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($varietyBreakdown as $variety)
                        <tr>
                            <td>{{ $variety->product_name }}</td>
                            <td>{{ $variety->brand ?: '—' }}</td>
                            <td class="numeric">{{ number_format($variety->quantity, 2) }}</td>
                            <td class="numeric">₱{{ number_format($variety->sales, 2) }}</td>
                            <td class="numeric">₱{{ number_format($variety->cost, 2) }}</td>
                            <td class="numeric">₱{{ number_format($variety->profit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                No completed items with recorded cost for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const salesData = {{ Illuminate\Support\Js::from(
        $dailySales->map(function ($day) {
            return [
                'date' => $day->sales_date,
                'total' => (float) $day->sales_total,
            ];
        })->values()
    ) }};

    const paymentLabels = {{ Illuminate\Support\Js::from($paymentLabels ?? []) }};
    const paymentData = {{ Illuminate\Support\Js::from($paymentData ?? []) }};

    const pesoFormatter = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2
    });
    const reportCharts = [];

    function updateReportChartTheme() {
        const chartTextColor = document.documentElement.dataset.theme === 'dark'
            ? '#d3dbed'
            : '#666';

        reportCharts.forEach(chart => {
            chart.options.color = chartTextColor;

            if (chart.options.scales) {
                Object.values(chart.options.scales).forEach(scale => {
                    scale.ticks.color = chartTextColor;
                    scale.grid.color = document.documentElement.dataset.theme === 'dark'
                        ? 'rgba(154, 165, 189, 0.18)'
                        : 'rgba(0, 0, 0, 0.1)';
                });
            }

            if (chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = chartTextColor;
            }

            chart.update();
        });
    }

    document.addEventListener('admin-theme-change', updateReportChartTheme);

    // Solid green Sales line graph
    const salesCanvas = document.getElementById('salesAnalyticsChart');

    if (salesCanvas) {
        reportCharts.push(new Chart(salesCanvas, {
            type: 'line',
            data: {
                labels: salesData.map(item => item.date),
                datasets: [{
                    label: 'Sales',
                    data: salesData.map(item => item.total),
                    borderColor: '#245b38',
                    backgroundColor: 'rgba(36, 91, 56, 0.10)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0,
                    pointBackgroundColor: '#245b38',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Sales: ' +
                                    pesoFormatter.format(context.parsed.y || 0);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 7
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            maxTicksLimit: 5,
                            callback: function (value) {
                                return '₱' + Number(value).toLocaleString('en-PH');
                            }
                        }
                    }
                }
            }
        }));
    }

    // Cash = green; GCash = yellow
    const paymentCanvas = document.getElementById('paymentMethodsChart');

    if (paymentCanvas) {
        const hasPaymentData =
            paymentLabels.length > 0 &&
            paymentData.some(value => Number(value) > 0);

        const paymentColors = paymentLabels.map(label => {
            const method = String(label).trim().toLowerCase();

            if (method === 'cash' || method === 'cod' ||
                method.includes('cash on delivery')) {
                return '#245b38';
            }

            if (method === 'gcash' || method === 'g cash') {
                return '#f4c542';
            }

            return '#9eaaa1';
        });

        reportCharts.push(new Chart(paymentCanvas, {
            type: 'pie',
            data: {
                labels: hasPaymentData ? paymentLabels : ['No sales data'],
                datasets: [{
                    data: hasPaymentData ? paymentData : [1],
                    backgroundColor: hasPaymentData ? paymentColors : ['#e5ebe6'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                if (!hasPaymentData) {
                                    return 'No sales data';
                                }

                                return context.label + ': ' +
                                    pesoFormatter.format(Number(context.raw) || 0);
                            }
                        }
                    }
                }
            }
        }));
    }

    updateReportChartTheme();
});
</script>
@endsection