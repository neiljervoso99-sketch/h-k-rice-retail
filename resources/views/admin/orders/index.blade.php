@extends('admin.layouts.app')

@section('title', 'Customer Orders')

@section('content')
    <div class="page-heading">
        <h1>Customer Orders</h1>
        <p>Review customer orders, manage fulfilment, and track inventory deductions.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Unable to process the order:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <h2>Order List</h2>
            <span class="muted">
                {{ $orders->total() }} total order(s)
            </span>
        </div>

        @if ($orders->isEmpty())
            <div class="empty-state">
                <h3>No Customer Orders</h3>
                <p class="muted">
                    Customer orders will appear here after checkout.
                </p>
            </div>
        @else
            <div class="table-wrap">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order Details</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Inventory</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>
                                    <strong class="order-number">
                                        {{ $order->order_number }}
                                    </strong>
                                    <div class="muted">
                                        {{ ucfirst($order->delivery_method ?? 'Not specified') }}
                                    </div>
                                    <div class="muted">
                                        {{ $order->created_at?->format('M d, Y h:i A') ?? 'N/A' }}
                                    </div>
                                </td>

                                <td>
                                    <strong class="customer-name">
                                        {{ $order->recipient_name ?? 'N/A' }}
                                    </strong>
                                    <div class="muted">
                                        {{ $order->customer?->email ?? 'Customer account unavailable' }}
                                    </div>
                                    @if ($order->phone)
                                        <div class="muted">{{ $order->phone }}</div>
                                    @endif
                                </td>

                                <td>
                                    @forelse ($order->items as $item)
                                        <div class="order-item">
                                            <strong>{{ $item->product_name }}</strong>
                                            <div class="muted">
                                                {{ number_format((float) $item->quantity, 2) }}
                                                {{ $item->unit_type === 'sack' ? 'sack(s)' : 'kg' }}
                                            </div>
                                            <div class="muted">
                                                ₱{{ number_format((float) $item->line_total, 2) }}
                                            </div>
                                        </div>
                                    @empty
                                        <span class="muted">No items</span>
                                    @endforelse
                                </td>

                                <td>
                                    <strong class="order-total">
                                        ₱{{ number_format((float) $order->total, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    @php
                                        $statusClass = match ($order->status) {
                                            'pending' => 'status-pending',
                                            'accepted' => 'status-accepted',
                                            'completed' => 'status-completed',
                                            'cancelled' => 'status-cancelled',
                                            default => 'status-default',
                                        };
                                    @endphp

                                    <span class="order-status {{ $statusClass }}">
                                        {{ $order->display_status }}
                                    </span>
                                </td>

                                <td>
                                    @if ($order->inventory_deducted_at)
                                        <span class="inventory-deducted">Deducted</span>
                                        <div class="muted">
                                            {{ $order->inventory_deducted_at->format('M d, Y h:i A') }}
                                        </div>
                                    @else
                                        <span class="inventory-not-deducted">
                                            Not deducted
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="order-actions">
                                        <a
                                            href="{{ route('admin.orders.show', $order) }}"
                                            class="button order-action-button"
                                        >
                                            See Order
                                        </a>

                                        @if ($order->status === 'accepted')
                                            <form
                                                method="POST"
                                                action="{{ route('admin.orders.complete', $order) }}"
                                                onsubmit="return confirm('Mark this order as completed?');"
                                            >
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="button order-action-button"
                                                >
                                                    Complete
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    @if ($order->admin_notes)
                                        <details class="admin-note">
                                            <summary>Admin Note</summary>
                                            <p>{{ $order->admin_notes }}</p>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                @php
                    $pageStart = max(1, $orders->currentPage() - 2);
                    $pageEnd = min($orders->lastPage(), $orders->currentPage() + 2);
                @endphp

                <nav class="orders-pagination" aria-label="Customer order pages">
                    @if ($orders->onFirstPage())
                        <span class="pagination-link is-disabled" aria-disabled="true">Previous</span>
                    @else
                        <a class="pagination-link" href="{{ $orders->previousPageUrl() }}">Previous</a>
                    @endif

                    @foreach (range($pageStart, $pageEnd) as $page)
                        @if ($page === $orders->currentPage())
                            <span class="pagination-link is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-link" href="{{ $orders->url($page) }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($orders->hasMorePages())
                        <a class="pagination-link" href="{{ $orders->nextPageUrl() }}">Next</a>
                    @else
                        <span class="pagination-link is-disabled" aria-disabled="true">Next</span>
                    @endif
                </nav>
            </div>
        @endif
    </div>

    <style>
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .panel-header h2 {
            margin: 0;
        }

        .muted {
            color: #777;
            font-size: 0.88rem;
        }

        .empty-state {
            padding: 35px 15px;
            text-align: center;
        }

        .empty-state h3 {
            margin-bottom: 8px;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 18px;
            border-radius: 8px;
        }

        .alert-success {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fecaca;
        }

        .alert ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .order-status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pending {
            color: #92400e;
            background: #fef3c7;
        }

        .status-accepted {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .status-completed {
            color: #166534;
            background: #dcfce7;
        }

        .status-cancelled {
            color: #991b1b;
            background: #fee2e2;
        }

        .status-default {
            color: #374151;
            background: #e5e7eb;
        }

        .inventory-deducted {
            color: #166534;
        }

        .inventory-not-deducted {
            color: #92400e;
            font-size: 0.85rem;
        }

        .order-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            min-width: 125px;
        }

        .order-actions form {
            margin: 0;
            width: 100%;
        }

        .order-actions button {
            width: 100%;
            white-space: nowrap;
            cursor: pointer;
        }

        .button-danger {
            background: #dc2626;
            color: #fff;
            border: 1px solid #dc2626;
        }

        .button-danger:hover {
            background: #b91c1c;
        }

        .pagination-wrap {
            margin-top: 20px;
        }

        .orders-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .pagination-link {
            display: inline-flex;
            min-width: 38px;
            min-height: 36px;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border: 1px solid #dfe6dc;
            border-radius: 6px;
            background: #fff;
            color: #245333;
            font-size: 13px;
            line-height: 1;
            text-decoration: none;
        }

        a.pagination-link:hover {
            background: #e8f1e5;
        }

        .pagination-link.is-current {
            border-color: #245333;
            background: #245333;
            color: #fff;
            font-weight: 700;
        }

        .pagination-link.is-disabled {
            color: #98a29a;
            cursor: not-allowed;
        }

        .table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .orders-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 11px;
        }

        .orders-table th,
        .orders-table td {
            padding: 8px 5px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .orders-table th {
            font-size: 10px;
            font-weight: 700;
            background: #f8fafc;
        }

        .orders-table th:nth-child(1) { width: 17%; }
        .orders-table th:nth-child(2) { width: 18%; }
        .orders-table th:nth-child(3) { width: 17%; }
        .orders-table th:nth-child(4) { width: 10%; }
        .orders-table th:nth-child(5) { width: 10%; }
        .orders-table th:nth-child(6) { width: 13%; }
        .orders-table th:nth-child(7) { width: 15%; }

        .order-number,
        .customer-name {
            display: block;
            overflow-wrap: anywhere;
        }

        .order-total {
            white-space: nowrap;
        }

        .order-item {
            margin-bottom: 7px;
        }

        .order-item:last-child {
            margin-bottom: 0;
        }

        .orders-table .muted {
            font-size: 10px;
            line-height: 1.4;
            margin-top: 3px;
        }

        .orders-table .order-status {
            white-space: normal;
            text-align: center;
            padding: 4px 6px;
            font-size: 10px;
        }

        .inventory-deducted,
        .inventory-not-deducted {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.3;
        }

        .order-actions {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 5px;
            min-width: 0;
        }

        .order-actions form {
            margin: 0;
            width: 100%;
        }

        .order-action-button {
            display: block;
            box-sizing: border-box;
            width: 100%;
            padding: 5px 4px;
            text-align: center;
            text-decoration: none;
            font-size: 10px;
            white-space: normal;
            line-height: 1.3;
        }

        .admin-note {
            margin-top: 6px;
            font-size: 10px;
            overflow-wrap: anywhere;
        }

        .admin-note summary {
            cursor: pointer;
            font-weight: 600;
        }

        .admin-note p {
            margin: 5px 0 0;
            color: #777;
            line-height: 1.4;
        }

        @media (max-width: 768px) {
            .panel-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .orders-table {
                font-size: 9px;
            }

            .orders-table th,
            .orders-table td {
                padding: 5px 3px;
            }

            .orders-table th {
                font-size: 9px;
            }

            .orders-table .muted {
                font-size: 9px;
            }

            .order-action-button {
                font-size: 9px;
            }
        }
    </style>
@endsection