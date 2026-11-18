 @extends('admin.layouts.app')

@section('content')
 <style>
     .transactions-pagination nav {
         display: flex;
         justify-content: flex-end;
     }

     .transactions-pagination nav > div:first-child {
         display: none !important;
     }

     .transactions-pagination nav > div:last-child {
         display: flex !important;
         align-items: center;
         justify-content: flex-end;
         width: 100%;
     }

     .transactions-pagination nav > div:last-child > div:first-child {
         display: none;
     }

     .transactions-pagination .pagination {
         display: flex;
         align-items: center;
         gap: 4px;
         padding: 0;
         margin: 0;
         list-style: none;
     }

     .transactions-pagination .page-link {
         display: block;
         min-width: 34px;
         padding: 7px 10px;
         border: 1px solid #dfe6dc;
         border-radius: 6px;
         color: #245333;
         text-align: center;
         text-decoration: none;
     }

     .transactions-pagination .page-item.active .page-link {
         border-color: #245333;
         background: #245333;
         color: #fff;
     }

     .transactions-pagination .page-item.disabled .page-link {
         color: #8a968c;
     }
 </style>

 <div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Manual Transactions</h1>
            <p class="text-muted mb-0">
                Manage walk-in sales and direct customer orders.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.sales.index') }}"
           style="display:inline-block;background:#fff;color:#198754;padding:10px 18px;border:1px solid #198754;border-radius:6px;text-decoration:none;font-weight:600;">
            ← Sales (Benta)
        </a>
        <a href="{{ route('admin.manual-transactions.create') }}"
           style="display:inline-block;background:#198754;color:#fff;padding:10px 18px;border:1px solid #198754;border-radius:6px;text-decoration:none;font-weight:600;">
            + New Transaction
        </a>
        </div>
    </div>

    {{-- Notifications --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <strong>Unable to save the transaction.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close float-end"
                    data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Transaction Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1">Transaction History</h5>
                    <small class="text-muted">
                        Review recorded manual sales and payment details.
                    </small>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2">
                    {{ $orders->total() }} Transactions
                </span>
            </div>

            @if ($orders->count())
                <div class="mt-3">
                    <input type="search"
                           id="transactionSearch"
                           class="form-control"
                           placeholder="Search by order number, customer, or phone..."
                           aria-label="Search transactions">
                </div>
            @endif
        </div>

        <div class="card-body p-0">
            @if ($orders->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="transactionsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3">Order Number</th>
                                <th class="py-3">Date</th>
                                <th class="py-3">Customer</th>
                                <th class="py-3">Items</th>
                                <th class="py-3">Payment</th>
                                <th class="py-3">GCash Reference Number</th>
                                <th class="py-3 text-end">Total</th>
                                <th class="py-3 text-end pe-4">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-semibold">
                                            {{ $order->order_number }}
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        {{ optional($order->completed_at)->format('M d, Y') ?? '—' }}
                                        <div class="small text-muted">
                                            {{ optional($order->completed_at)->format('h:i A') ?? '' }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $order->recipient_name }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $order->phone ?: 'No phone number' }}
                                        </small>
                                    </td>

                                    <td>
                                        @foreach ($order->items as $item)
                                            <div class="mb-1">
                                                <span>{{ $item->product_name }}</span>
                                                <small class="text-muted d-block">
                                                    {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                                                    {{ $item->unit_type === 'sack' ? 'sack(s)' : $item->unit_type }}
                                                </small>
                                            </div>
                                        @endforeach
                                    </td>

                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle mb-1">
                                            {{ strtoupper($order->payment_method ?: 'N/A') }}
                                        </span>
                                        <div class="small text-muted">
                                            {{ ucfirst($order->payment_status ?: 'Unknown') }}
                                        </div>
                                    </td>

                                    <td>
                                        @if (strtolower(trim((string) $order->payment_method)) === 'gcash')
                                            @php
                                                $gcashPayment = $order->payments->first(
                                                    fn ($payment) => strtolower(trim((string) $payment->method)) === 'gcash'
                                                );
                                            @endphp

                                            @if ($gcashPayment && $gcashPayment->reference_number)
                                                <span class="fw-semibold">
                                                    {{ $gcashPayment->reference_number }}
                                                </span>
                                            @else
                                                <span class="small text-danger">Not recorded</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">
                                        <span class="fw-bold text-nowrap">
                                            ₱{{ number_format((float) $order->total, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a class="btn btn-sm btn-outline-success"
                                           href="{{ route('admin.manual-transactions.receipt', $order) }}">
                                            View / Print
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tbody id="noSearchResults" style="display: none;">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No matching transactions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-top">
                    <small class="text-muted">
                        Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }}
                        of {{ $orders->total() }} transactions
                    </small>
                    <div class="transactions-pagination">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="text-center py-5 px-3">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light"
                              style="width: 72px; height: 72px; font-size: 30px;">
                            🧾
                        </span>
                    </div>
                    <h5 class="fw-bold">No Manual Transactions Yet</h5>
                    <p class="text-muted mb-4">
                        Record your first walk-in sale or direct customer order.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Table Search --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('transactionSearch');
    const table = document.getElementById('transactionsTable');
    const noResults = document.getElementById('noSearchResults');

    if (!searchInput || !table || !noResults) {
        return;
    }

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        const rows = table.querySelectorAll('tbody:not(#noSearchResults) tr');
        let visibleCount = 0;

        rows.forEach(function (row) {
            const matches = row.textContent.toLowerCase().includes(query);
            row.style.display = matches ? '' : 'none';

            if (matches) {
                visibleCount++;
            }
        });

        noResults.style.display = visibleCount === 0 ? '' : 'none';
    });
});
</script>
@endsection