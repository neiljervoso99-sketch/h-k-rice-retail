@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<style>
    .store-dashboard {
        --dashboard-green: #a855f7;
        --dashboard-green-dark: #7c3aed;
        --dashboard-green-soft: rgba(52, 211, 153, .13);
        --dashboard-border: #293149;
        --dashboard-muted: #9aa5bd;
        --dashboard-shell: #0d1323;
        --dashboard-heading-bg: #131b2e;
        --dashboard-panel-bg: #131b2e;
        --dashboard-kpi-bg: linear-gradient(145deg, #151e32, #11192a);
        --dashboard-text: #ecf1ff;
        --dashboard-heading-text: #f2f5ff;
        --dashboard-kpi-label: #aab5cb;
        --dashboard-kpi-value: #f1f5ff;
        --dashboard-row-bg: #1a2439;
        --dashboard-row-text: #d3dbed;
        --dashboard-table-line: #252e43;
        --dashboard-order-link: #c4b5fd;
        --dashboard-status-text: #6ee7b7;
        --dashboard-table-heading: #131b2e;
        min-width: 0;
        padding: 18px;
        border: 1px solid #222b40;
        border-radius: 18px;
        background:
            radial-gradient(ellipse at 82% 0%, rgba(86, 54, 150, .18), transparent 36%),
            var(--dashboard-shell);
        color: var(--dashboard-text);
        box-shadow: 0 16px 38px rgba(13, 19, 35, .14);
    }

    :root[data-theme="light"] .store-dashboard {
        --dashboard-green: #7c3aed;
        --dashboard-green-dark: #6d28d9;
        --dashboard-green-soft: #e8f6ef;
        --dashboard-border: #e2e8e4;
        --dashboard-muted: #748078;
        --dashboard-shell: #f3f6f4;
        --dashboard-heading-bg: #fff;
        --dashboard-panel-bg: #fff;
        --dashboard-kpi-bg: #fff;
        --dashboard-text: #263b2b;
        --dashboard-heading-text: #203d2c;
        --dashboard-kpi-label: #53645a;
        --dashboard-kpi-value: #1d2b22;
        --dashboard-row-bg: #f7faf8;
        --dashboard-row-text: #34443a;
        --dashboard-table-line: #eef2ef;
        --dashboard-order-link: #6d28d9;
        --dashboard-status-text: #116b42;
        --dashboard-table-heading: #fff;
        border-color: #e5ece7;
        background: var(--dashboard-shell);
        color: var(--dashboard-text);
        box-shadow: 0 8px 24px rgba(27, 55, 34, .08);
    }

    .store-dashboard * {
        box-sizing: border-box;
    }

    .dashboard-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        margin-bottom: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: var(--dashboard-heading-bg);
    }

    .dashboard-heading h1 {
        margin: 0 0 4px;
        color: var(--dashboard-heading-text);
        font-size: 22px;
    }

    .dashboard-heading p {
        margin: 0;
        color: var(--dashboard-muted);
        font-size: 12px;
    }

    .dashboard-live {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 7px 10px;
        border: 1px solid rgba(52, 211, 153, .24);
        border-radius: 20px;
        background: rgba(16, 185, 129, .1);
        color: #6ee7b7;
        font-size: 11px;
        font-weight: 700;
    }

    .dashboard-live::before {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #34d399;
        content: "";
    }

    .dashboard-heading-tools {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }

    .dashboard-clock {
        color: var(--dashboard-muted);
        text-align: right;
    }

    .dashboard-clock-label {
        display: block;
        font-size: 10px;
    }

    .dashboard-clock time {
        display: block;
        margin-top: 2px;
        color: var(--dashboard-heading-text);
        font-size: 13px;
        font-weight: 700;
    }

    .dashboard-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .dashboard-kpi {
        min-width: 0;
        padding: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: var(--dashboard-kpi-bg);
        box-shadow: 0 6px 16px rgba(0, 0, 0, .12);
    }

    .dashboard-kpi.primary {
        border-color: rgba(192, 132, 252, .5);
        background:
            radial-gradient(ellipse at 100% 0%, rgba(192, 132, 252, .3), transparent 48%),
            linear-gradient(135deg, #582d91, #39245f);
        color: #fff;
    }

    .dashboard-kpi-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--dashboard-kpi-label);
        font-size: 12px;
        font-weight: 600;
    }

    .dashboard-kpi.primary .dashboard-kpi-label {
        color: #f3e8ff;
    }

    .dashboard-kpi-value {
        display: block;
        overflow-wrap: anywhere;
        color: var(--dashboard-kpi-value);
        font-size: clamp(20px, 2vw, 26px);
        font-weight: 800;
        line-height: 1.2;
    }

    .dashboard-kpi.primary .dashboard-kpi-value {
        color: #fff;
    }

    .dashboard-kpi-note {
        display: block;
        margin-top: 7px;
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .dashboard-kpi.primary .dashboard-kpi-note {
        color: #e9d5ff;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(260px, .9fr);
        gap: 14px;
        align-items: stretch;
        margin-bottom: 14px;
    }

    .dashboard-panel {
        min-width: 0;
        padding: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: var(--dashboard-panel-bg);
    }

    .dashboard-panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .dashboard-panel-heading h2 {
        margin: 0;
        color: var(--dashboard-heading-text);
        font-size: 15px;
    }

    .dashboard-panel-heading small {
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .dashboard-chart {
        position: relative;
        width: 100%;
        height: 235px;
    }

    .dashboard-chart.doughnut {
        height: 210px;
        max-width: 300px;
        margin: 0 auto;
    }

    .category-legend {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px 12px;
        margin-top: 12px;
    }

    .category-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-width: 0;
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .category-legend-item span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .category-legend-item strong {
        color: var(--dashboard-heading-text);
        white-space: nowrap;
    }

    .dashboard-lower-grid {
        display: grid;
        grid-template-columns: minmax(220px, .8fr) minmax(0, 1.7fr);
        gap: 14px;
        align-items: start;
        margin-bottom: 14px;
    }

    .top-product-list {
        display: grid;
        gap: 8px;
    }

    .top-product {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px;
        border-radius: 8px;
        background: var(--dashboard-row-bg);
    }

    .top-product-name {
        min-width: 0;
        color: var(--dashboard-heading-text);
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .top-product-quantity {
        display: block;
        margin-top: 3px;
        color: var(--dashboard-muted);
        font-size: 10px;
        font-weight: 400;
    }

    .top-product-sales {
        flex-shrink: 0;
        color: var(--dashboard-green-dark);
        font-size: 12px;
        font-weight: 700;
    }

    .dashboard-orders-wrap {
        overflow-x: auto;
    }

    .dashboard-orders {
        width: 100%;
        min-width: 500px;
        border-collapse: collapse;
    }

    .dashboard-orders th {
        padding: 9px 8px;
        border-bottom: 1px solid var(--dashboard-border);
        background: var(--dashboard-table-heading);
        color: var(--dashboard-muted);
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .dashboard-orders td {
        padding: 10px 8px;
        border-bottom: 1px solid var(--dashboard-table-line);
        color: var(--dashboard-row-text);
        font-size: 11px;
        vertical-align: middle;
    }

    .dashboard-orders tbody tr:last-child td {
        border-bottom: 0;
    }

    .dashboard-order-number {
        color: var(--dashboard-order-link);
        font-weight: 700;
    }

    .dashboard-status {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 20px;
        background: var(--dashboard-green-soft);
        color: var(--dashboard-status-text);
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .dashboard-status.pending,
    .dashboard-status.accepted {
        background: rgba(251, 191, 36, .12);
        color: #fcd34d;
    }

    .dashboard-status.cancelled {
        background: rgba(248, 113, 113, .12);
        color: #fca5a5;
    }

    .dashboard-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .dashboard-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 12px;
        border: 1px solid #245333;
        border-radius: 6px;
        background: #245333;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .dashboard-action-button:hover,
    .dashboard-action-button:focus-visible {
        border-color: #193d25;
        background: #193d25;
        color: #fff;
    }

    .dashboard-empty {
        padding: 18px 10px;
        color: var(--dashboard-muted);
        font-size: 12px;
        text-align: center;
    }

    .dashboard-status-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }

    .dashboard-status-count {
        padding: 11px;
        border: 1px solid var(--dashboard-border);
        border-radius: 8px;
        background: #151e31;
    }

    .dashboard-status-count span {
        display: block;
        color: var(--dashboard-muted);
        font-size: 10px;
    }

    .dashboard-status-count strong {
        display: block;
        margin-top: 4px;
        color: #f0f4ff;
        font-size: 17px;
    }

    @media (max-width: 1050px) {
        .dashboard-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid {
            grid-template-columns: minmax(0, 1.3fr) minmax(240px, .9fr);
        }
    }

    @media (max-width: 760px) {
        .dashboard-grid,
        .dashboard-lower-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .dashboard-chart.doughnut {
            height: 190px;
        }
    }

    @media (max-width: 520px) {
        .store-dashboard {
            padding: 10px;
            border-radius: 14px;
        }

        .dashboard-heading {
            align-items: flex-start;
            padding: 14px;
        }

        .dashboard-heading h1 {
            font-size: 19px;
        }

        .dashboard-live {
            padding: 6px 8px;
            font-size: 10px;
        }

        .dashboard-heading-tools {
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .dashboard-clock-label {
            font-size: 9px;
        }

        .dashboard-clock time {
            font-size: 11px;
        }

        .dashboard-kpis {
            gap: 8px;
        }

        .dashboard-kpi {
            padding: 12px;
        }

        .dashboard-kpi-label {
            font-size: 10px;
        }

        .dashboard-kpi-note {
            font-size: 10px;
        }

        .dashboard-status-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-chart {
            height: 205px;
        }

        .dashboard-panel {
            padding: 13px;
        }
    }
</style>

<div class="store-dashboard">
    <header class="dashboard-heading">
        <div>
            <h1>Dashboard Overview</h1>
            <p>
                @if (auth()->user()->isAdmin())
                    Store analytics, performance, and recent activity at a glance.
                @else
                    Operational overview, product activity, and low-stock alerts.
                @endif
            </p>
        </div>
        <div class="dashboard-heading-tools">
            <div class="dashboard-clock">
                <span class="dashboard-clock-label">Philippine time · Asia/Manila</span>
                <time
                    id="manila-clock"
                    datetime="{{ now('Asia/Manila')->toIso8601String() }}"
                >{{ now('Asia/Manila')->format('M d, Y · g:i:s A') }}</time>
            </div>
            <span class="dashboard-live">Live data</span>
        </div>
    </header>

    <section class="dashboard-kpis" aria-label="Store overview">
        @if (auth()->user()->isAdmin())
            <article class="dashboard-kpi primary">
                <span class="dashboard-kpi-label">Revenue this month</span>
                <strong class="dashboard-kpi-value">₱{{ number_format((float) $monthSales, 2) }}</strong>
                <span class="dashboard-kpi-note">₱{{ number_format((float) $weekSales, 2) }} this week · ₱{{ number_format((float) $todaySales, 2) }} today</span>
            </article>
        @endif
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Orders this month</span>
            <strong class="dashboard-kpi-value">{{ number_format($ordersThisMonth) }}</strong>
            <span class="dashboard-kpi-note">{{ number_format($pendingOrders) }} waiting for action</span>
        </article>
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Active products</span>
            <strong class="dashboard-kpi-value">{{ number_format($activeProductCount) }}</strong>
            <span class="dashboard-kpi-note">{{ number_format($lowStockCount) }} low-stock alerts</span>
        </article>
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Customers</span>
            <strong class="dashboard-kpi-value">{{ number_format($customerCount) }}</strong>
            <span class="dashboard-kpi-note">Registered customer accounts</span>
        </article>
    </section>

    @if (auth()->user()->isAdmin())
    <section class="dashboard-grid">
        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Sales Trend</h2>
                <small>Completed sales · last 7 days</small>
            </div>
            <div class="dashboard-chart">
                <canvas id="salesTrendsChart" aria-label="Sales over the last seven days"></canvas>
            </div>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Sales by Category</h2>
                <small>This month</small>
            </div>
            @if ($salesByCategory->isNotEmpty())
                <div class="dashboard-chart doughnut">
                    <canvas id="categorySalesChart" aria-label="Sales split by product category"></canvas>
                </div>
                <div class="category-legend">
                    @foreach ($salesByCategory as $categorySale)
                        <div class="category-legend-item">
                            <span>{{ $categorySale->category }}</span>
                            <strong>₱{{ number_format((float) $categorySale->total, 0) }}</strong>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty">No completed category sales this month yet.</div>
            @endif
        </article>
    </section>
    @endif

    <section class="dashboard-lower-grid">
        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Top Products</h2>
                <small>This month</small>
            </div>
            @if ($topProducts->isNotEmpty())
                <div class="top-product-list">
                    @foreach ($topProducts as $product)
                        <div class="top-product">
                            <div class="top-product-name">
                                {{ $product->product_name }}
                                <span class="top-product-quantity">{{ number_format((float) $product->quantity_sold, 0) }} sold</span>
                            </div>
                            @if (auth()->user()->isAdmin())
                                <span class="top-product-sales">₱{{ number_format((float) $product->sales_total, 2) }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty">No completed product sales this month yet.</div>
            @endif
        </article>

        @if (auth()->user()->isAdmin())
        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Recent Orders</h2>
                <a class="dashboard-action-button" href="{{ route('admin.orders.index') }}">All orders</a>
            </div>
            @if ($recentOrders->isNotEmpty())
                <div class="dashboard-orders-wrap">
                    <table class="dashboard-orders">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a class="dashboard-order-number" href="{{ route('admin.orders.show', $order) }}">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->recipient_name ?: ($order->customer?->name ?? 'Customer') }}</td>
                                    <td>{{ $order->created_at?->format('M d') }}</td>
                                    <td><span class="dashboard-status {{ strtolower($order->status) }}">{{ ucfirst($order->status) }}</span></td>
                                    <td>₱{{ number_format((float) $order->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="dashboard-empty">No orders have been placed yet.</div>
            @endif
        </article>
        @endif
    </section>

    @if (auth()->user()->isAdmin())
        <nav class="dashboard-actions" aria-label="Dashboard shortcuts">
            <a class="dashboard-action-button" href="{{ route('admin.orders.index') }}">Manage Orders</a>
            <a class="dashboard-action-button" href="{{ route('admin.inventory.index') }}">Inventory</a>
            <a class="dashboard-action-button" href="{{ route('admin.products.index') }}">Products</a>
            <a class="dashboard-action-button" href="{{ route('admin.reports.index') }}">Reports</a>
            <a class="dashboard-action-button" href="{{ route('admin.expenses.index') }}">Expenses</a>
        </nav>
    @else
        <nav class="dashboard-actions" aria-label="Dashboard shortcuts">
            <a class="dashboard-action-button" href="{{ route('admin.manual-transactions.index') }}">Manual Transactions</a>
        </nav>
    @endif

    <section class="dashboard-status-summary" aria-label="Order status totals">
        <div class="dashboard-status-count"><span>Pending</span><strong>{{ number_format($pendingOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Accepted</span><strong>{{ number_format($acceptedOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Completed</span><strong>{{ number_format($completedOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Cancelled</span><strong>{{ number_format($cancelledOrders) }}</strong></div>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <h2>Low Stock Alerts</h2>
            @if (auth()->user()->isAdmin())
                <a class="dashboard-action-button" href="{{ route('admin.inventory.index') }}">Open inventory</a>
            @endif
        </div>
        @if ($lowStockProducts->isNotEmpty())
            <div class="dashboard-orders-wrap">
                <table class="dashboard-orders">
                    <thead>
                        <tr><th>Product</th><th>Available stock</th><th>Alert threshold</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStockProducts as $product)
                            @php
                                $isGenericProduct = ($product->inventory_type ?? 'rice') === 'generic';
                                $stockQuantity = (float) ($isGenericProduct ? $product->stock_base_quantity : $product->stock_kg);
                                $stockUnit = $isGenericProduct
                                    ? ($product->base_unit ?: $product->stock_unit ?: 'unit')
                                    : 'kg';
                                $thresholdQuantity = (float) ($isGenericProduct
                                    ? $product->low_stock_threshold_quantity
                                    : $product->low_stock_threshold);
                            @endphp
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format($stockQuantity, 2) }} {{ $stockUnit }}</td>
                                <td>{{ number_format($thresholdQuantity, 2) }} {{ $stockUnit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="dashboard-empty">No active products are currently at or below their stock threshold.</div>
        @endif
    </section>
</div>

@if (auth()->user()->isAdmin())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const manilaClock = document.getElementById('manila-clock');
        if (manilaClock) {
            const dateFormatter = new Intl.DateTimeFormat('en-PH', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
            const timeFormatter = new Intl.DateTimeFormat('en-PH', {
                timeZone: 'Asia/Manila',
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            const updateManilaClock = function () {
                const currentTime = new Date();
                manilaClock.dateTime = currentTime.toISOString();
                manilaClock.textContent = dateFormatter.format(currentTime)
                    + ' · ' + timeFormatter.format(currentTime);
            };

            updateManilaClock();
            window.setInterval(updateManilaClock, 1000);
        }

        @if (auth()->user()->isAdmin())
        if (typeof Chart === 'undefined') {
            return;
        }

        const charts = [];
        const isDarkTheme = function () {
            return document.documentElement.dataset.theme === 'dark';
        };

        const salesCanvas = document.getElementById('salesTrendsChart');
        if (salesCanvas) {
            const salesChart = new Chart(salesCanvas, {
                type: 'line',
                data: {
                    labels: @json($salesChartLabels),
                    datasets: [{
                        label: 'Sales (₱)',
                        data: @json($salesChartData),
                        borderColor: isDarkTheme() ? '#c084fc' : '#7c3aed',
                        backgroundColor: isDarkTheme()
                            ? 'rgba(168, 85, 247, .18)'
                            : 'rgba(124, 58, 237, .12)',
                        fill: true,
                        tension: .38,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: isDarkTheme() ? '#67e8f9' : '#7c3aed',
                        pointBorderColor: isDarkTheme() ? '#131b2e' : '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '₱' + Number(context.parsed.y).toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: isDarkTheme() ? '#9aa5bd' : '#748078' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: isDarkTheme()
                                    ? 'rgba(154, 165, 189, .14)'
                                    : 'rgba(116, 128, 120, .18)'
                            },
                            ticks: {
                                color: isDarkTheme() ? '#9aa5bd' : '#748078',
                                callback: function (value) {
                                    return '₱' + Number(value).toLocaleString('en-PH');
                                }
                            }
                        }
                    }
                }
            });
            charts.push(salesChart);
        }

        const categoryCanvas = document.getElementById('categorySalesChart');
        if (categoryCanvas) {
            const categoryChart = new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: @json($salesByCategory->pluck('category')->values()),
                    datasets: [{
                        data: @json($salesByCategory->pluck('total')->map(fn ($total) => (float) $total)->values()),
                        backgroundColor: ['#c084fc', '#22d3ee', '#818cf8', '#34d399', '#f472b6', '#fbbf24'],
                        borderWidth: 3,
                        borderColor: isDarkTheme() ? '#131b2e' : '#fff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '66%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ₱' + Number(context.parsed).toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
            charts.push(categoryChart);
        }

        document.addEventListener('admin-theme-change', function (event) {
            const isDark = event.detail.theme === 'dark';

            charts.forEach(function (chart) {
                if (chart.config.type === 'line') {
                    chart.data.datasets[0].backgroundColor = isDark
                        ? 'rgba(168, 85, 247, .18)'
                        : 'rgba(124, 58, 237, .12)';
                    chart.data.datasets[0].pointBackgroundColor = isDark
                        ? '#67e8f9'
                        : '#7c3aed';
                    chart.data.datasets[0].pointBorderColor = isDark
                        ? '#131b2e'
                        : '#fff';
                    chart.options.scales.x.ticks.color = isDark
                        ? '#9aa5bd'
                        : '#748078';
                    chart.options.scales.y.ticks.color = isDark
                        ? '#9aa5bd'
                        : '#748078';
                    chart.options.scales.y.grid.color = isDark
                        ? 'rgba(154, 165, 189, .14)'
                        : 'rgba(116, 128, 120, .18)';
                } else {
                    chart.data.datasets[0].borderColor = isDark ? '#131b2e' : '#fff';
                }

                chart.update();
            });
        });
        @endif
    });

    window.setTimeout(function () {
        window.location.reload();
    }, 60000);
</script>
@endsection
