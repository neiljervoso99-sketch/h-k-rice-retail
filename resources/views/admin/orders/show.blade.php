
@extends('admin.layouts.app')

@section('title', 'Order Details')

@section('content')
    <div class="page-heading">
        <h1>Order Details</h1>
        <p>Review the customer's information and order before making a decision.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Unable to process the order:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $statusClass = match ($order->status) {
            'pending' => 'pending',
            'accepted' => 'accepted',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'other',
        };

        $fullAddress = collect([
            $order->address_line,
            $order->barangay,
            $order->municipality,
            $order->province,
            $order->postal_code,
        ])->filter(fn ($value) => filled($value))->implode(', ');
    @endphp

    <div class="details-actions">
        <a class="button button-secondary" href="{{ route('admin.orders.index') }}">
            Back to Customer Orders
        </a>

        <span class="status-badge status-{{ $statusClass }}">
            {{ $order->display_status }}
        </span>
    </div>

    <section class="panel">
        <h2>Order Information</h2>

        <div class="details-grid">
            <div>
                <span class="field-label">Order Number</span>
                <strong>{{ $order->order_number }}</strong>
            </div>

            <div>
                <span class="field-label">Order Date</span>
                <strong>{{ $order->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</strong>
            </div>

            <div>
                <span class="field-label">Order Status</span>
                <strong>{{ $order->display_status }}</strong>
            </div>

            <div>
                <span class="field-label">Processing</span>
                <strong>{{ $order->processing_at?->format('M d, Y h:i A') ?? 'Not started' }}</strong>
            </div>

            <div>
                <span class="field-label">Shipped</span>
                <strong>{{ $order->shipped_at?->format('M d, Y h:i A') ?? 'Not shipped' }}</strong>
            </div>

            <div>
                <span class="field-label">Delivered</span>
                <strong>{{ $order->delivered_at?->format('M d, Y h:i A') ?? 'Not delivered' }}</strong>
            </div>

            <div>
                <span class="field-label">Delivery Method</span>
                <strong>{{ $order->delivery_method ?: 'Not specified' }}</strong>
            </div>

            <div>
                <span class="field-label">Delivery Area</span>
                <strong>{{ $order->delivery_area ?: 'Not specified' }}</strong>
            </div>

            <div>
                <span class="field-label">Payment Method</span>
                <strong>{{ $order->payment_method ?: 'Not specified' }}</strong>
            </div>

            <div>
                <span class="field-label">Payment Status</span>
                <strong>{{ $order->payment_status ?: 'Not specified' }}</strong>
            </div>

            <div>
                <span class="field-label">Inventory</span>
                <strong>
                    {{ $order->inventory_deducted_at ? 'Deducted' : 'Not deducted' }}
                </strong>

                @if ($order->inventory_deducted_at)
                    <small>{{ $order->inventory_deducted_at->format('M d, Y h:i A') }}</small>
                @endif
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Customer and Delivery Information</h2>

        <div class="details-grid">
            <div>
                <span class="field-label">Recipient Name</span>
                <strong>{{ $order->recipient_name ?: 'N/A' }}</strong>
            </div>

            <div>
                <span class="field-label">Customer Account</span>
                <strong>{{ $order->customer?->name ?? 'Account unavailable' }}</strong>
                <small>{{ $order->customer?->email ?? 'No email available' }}</small>
            </div>

            <div>
                <span class="field-label">Phone Number</span>
                <strong>{{ $order->phone ?: 'Not provided' }}</strong>
            </div>

            <div>
                <span class="field-label">Delivery Address</span>
                <strong>{{ $fullAddress ?: 'No address provided' }}</strong>
            </div>

            @if ($order->delivery_latitude !== null && $order->delivery_longitude !== null)
                <div>
                    <span class="field-label">Delivery Coordinates</span>
                    <strong>{{ $order->delivery_latitude }}, {{ $order->delivery_longitude }}</strong>
                </div>
            @endif

            <div>
                <span class="field-label">Receipt Requested</span>
                <strong>{{ $order->receipt_requested ? 'Yes' : 'No' }}</strong>
            </div>

            @if ($order->receipt_file)
                <div>
                    <span class="field-label">Receipt File</span>
                    <strong>{{ $order->receipt_file }}</strong>
                </div>
            @endif
        </div>

        @if ($order->customer_notes)
            <div class="note-block">
                <span class="field-label">Customer Notes</span>
                <p>{{ $order->customer_notes }}</p>
            </div>
        @endif

        @if ($order->delivery_notes)
            <div class="note-block">
                <span class="field-label">Delivery Notes</span>
                <p>{{ $order->delivery_notes }}</p>
            </div>
        @endif

        @if ($order->admin_notes)
            <div class="note-block">
                <span class="field-label">Admin Notes</span>
                <p>{{ $order->admin_notes }}</p>
            </div>
        @endif
    </section>

    <section class="panel">
        <h2>Ordered Products</h2>

        @if ($order->items->isEmpty())
            <p>No products are recorded for this order.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->brand ?: 'N/A' }}</td>
                                <td>
                                    {{ number_format((float) $item->quantity, 2) }}
                                    {{ $item->unit_type === 'sack' ? 'sack(s)' : 'kg' }}

                                    @if ($item->quantity_kg)
                                        <small>
                                            ({{ number_format((float) $item->quantity_kg, 2) }} kg in stock units)
                                        </small>
                                    @endif
                                </td>
                                <td>₱{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>
                                    <strong>₱{{ number_format((float) $item->line_total, 2) }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="totals">
            <div>
                <span>Subtotal</span>
                <strong>₱{{ number_format((float) $order->subtotal, 2) }}</strong>
            </div>
            <div>
                <span>Delivery Fee</span>
                <strong>₱{{ number_format((float) $order->delivery_fee, 2) }}</strong>
            </div>
            <div class="grand-total">
                <span>Order Total</span>
                <strong>₱{{ number_format((float) $order->total, 2) }}</strong>
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Payment Records</h2>

        @if ($order->payments->isEmpty())
            <p>No separate payment records are available for this order.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Payment Details</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Verification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->payments as $payment)
                            <tr>
                                <td>
                                    <strong>Payment #{{ $payment->id }}</strong>
                                    <small>Method: {{ strtoupper($payment->method ?? 'N/A') }}</small>
                                    <small>Reference: {{ $payment->reference_number ?: 'Not provided' }}</small>
                                    <small>
                                        Submitted: {{ $payment->created_at?->format('M d, Y h:i A') ?? 'N/A' }}
                                    </small>
                                </td>

                                <td>₱{{ number_format((float) ($payment->amount ?? 0), 2) }}</td>

                                <td>
                                    <strong>{{ ucfirst($payment->status ?? 'Unknown') }}</strong>
                                </td>

                                <td>
                                    @if ($payment->verified_at)
                                        <strong>Verified</strong>
                                        <small>{{ $payment->verified_at->format('M d, Y h:i A') }}</small>
                                        <small>By: {{ $payment->verifiedBy?->name ?? 'Admin account unavailable' }}</small>
                                    @else
                                        <span>Not verified</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="payment-actions">
                                        @if (strtolower((string) $payment->method) === 'gcash')
                                            @if ($payment->proof_path)
                                                <a
                                                    class="button button-secondary"
                                                    href="{{ route('admin.orders.payments.proof', [$order, $payment]) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    View Payment Proof
                                                </a>
                                            @else
                                                <span class="muted">No proof uploaded</span>
                                            @endif

                                            @if (
                                                $order->status === 'pending'
                                                && $payment->status === 'pending'
                                                && filled($payment->reference_number)
                                                && filled($payment->proof_path)
                                            )
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.orders.payments.verify', [$order, $payment]) }}"
                                                    onsubmit="return confirm('Confirm that you have checked the GCash reference and payment proof and verified the payment?');"
                                                >
                                                    @csrf
                                                    <button type="submit" class="button">Verify Payment</button>
                                                </form>

                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.orders.payments.reject', [$order, $payment]) }}"
                                                    onsubmit="return confirm('Reject this GCash payment? The order will not be approved.');"
                                                >
                                                    @csrf
                                                    <button type="submit" class="button button-danger">Reject Payment</button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="muted">No GCash verification required</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="panel">
        <h2>Rider Assignment</h2>

        <div class="details-grid">
            <div>
                <span class="field-label">Assigned Rider</span>
                <strong>{{ $order->rider?->name ?? 'Not assigned' }}</strong>
            </div>

            <div>
                <span class="field-label">Assigned By</span>
                <strong>{{ $order->assignedBy?->name ?? 'N/A' }}</strong>
            </div>

            <div>
                <span class="field-label">Assignment Date</span>
                <strong>{{ $order->assigned_at?->format('M d, Y h:i A') ?? 'Not assigned' }}</strong>
            </div>
        </div>

        @if ($order->delivery_proof_path)
            <div class="note-block">
                <span class="field-label">Delivery Proof</span>
                <p>
                    Uploaded:
                    {{ $order->delivery_proof_uploaded_at?->format('M d, Y h:i A') ?? 'Time unavailable' }}
                </p>
                <a
                    class="button button-secondary"
                    href="{{ route('admin.orders.delivery-proof', $order) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    View Delivery Proof
                </a>
            </div>
        @endif

        @if ($order->status === 'accepted' && !$order->delivery_proof_path)
            <form method="POST" action="{{ route('admin.orders.assign-rider', $order) }}" class="assignment-form">
                @csrf

                <label for="rider_id" class="field-label">Select Rider</label>

                <select id="rider_id" name="rider_id" required>
                    <option value="">Choose a rider</option>

                    @foreach ($riders as $rider)
                        <option
                            value="{{ $rider->id }}"
                            @selected((int) old('rider_id', $order->rider_id) === (int) $rider->id)
                        >
                            {{ $rider->name }} — {{ $rider->email }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="button" @disabled($riders->isEmpty())>
                    Assign Rider
                </button>

                @if ($riders->isEmpty())
                    <p class="muted">No rider accounts are available. Create a user account with the rider role first.</p>
                @endif
            </form>
        @elseif ($order->delivery_proof_path)
            <p class="muted">Rider reassignment is disabled because delivery proof has already been uploaded.</p>
        @elseif ($order->status === 'pending')
            <p class="muted">Approve the order before assigning a rider.</p>
        @else
            <p class="muted">Only accepted orders can be assigned to a rider.</p>
        @endif
    </section>

    @if ($order->status === 'pending')
        <section class="panel decision-panel">
            <h2>Order Decision</h2>
            <p>Review all order details before approving or rejecting this order.</p>

            <div class="decision-actions">
                <form
                    method="POST"
                    action="{{ route('admin.orders.accept', $order) }}"
                    onsubmit="return confirm('Approve this order? Required stock will be deducted from inventory.');"
                >
                    @csrf
                    <button type="submit" class="button">Approve Order</button>
                </form>

                <form
                    method="POST"
                    action="{{ route('admin.orders.reject', $order) }}"
                    onsubmit="return confirm('Reject this order? Inventory will not be deducted.');"
                >
                    @csrf
                    <button type="submit" class="button button-danger">Reject Order</button>
                </form>
            </div>
        </section>
    @elseif ($order->status === 'accepted')
        <section class="panel">
            <h2>Order Fulfilment</h2>
            <p>This order has been approved.</p>

            <form
                method="POST"
                action="{{ route('admin.orders.complete', $order) }}"
                onsubmit="return confirm('Mark this order as completed?');"
            >
                @csrf
                <button type="submit" class="button">Complete Order</button>
            </form>
        </section>
    @else
        <section class="panel">
            <h2>Order Decision</h2>
            <p>No further approval actions are available for this order.</p>
        </section>
    @endif

    <style>
        .panel {
            background: #fff;
            padding: 22px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
        }

        .panel h2 { margin: 0 0 18px; }

        .details-actions,
        .decision-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .details-grid > div {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
            overflow-wrap: anywhere;
        }

        .field-label, .muted { color: #6b7280; font-size: .88rem; }

        .details-grid small, td small {
            display: block;
            color: #6b7280;
            margin-top: 4px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: .85rem;
            font-weight: 600;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-accepted { background: #dbeafe; color: #1d4ed8; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-other { background: #e5e7eb; color: #374151; }

        .table-wrap { width: 100%; overflow-x: auto; }
        .table-wrap table { width: 100%; border-collapse: collapse; }

        .table-wrap th, .table-wrap td {
            padding: 12px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
        }

        .totals { max-width: 380px; margin: 20px 0 0 auto; }

        .totals > div {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 9px 0;
        }

        .grand-total { border-top: 2px solid #e5e7eb; font-size: 1.1rem; }

        .note-block {
            margin-top: 20px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .note-block p { margin-bottom: 0; }

        .button-secondary {
            display: inline-block;
            background: #f3f4f6;
            color: #111827;
            text-decoration: none;
        }

        .button-danger {
            background: #dc2626;
            color: #fff;
            border: 1px solid #dc2626;
        }

        .button-danger:hover { background: #b91c1c; }
        .decision-actions form { margin: 0; }

        .payment-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            min-width: 160px;
        }

        .payment-actions form { margin: 0; }

        .payment-actions .button { white-space: nowrap; }

        .assignment-form {
            margin-top: 18px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .assignment-form select {
            width: 100%;
            max-width: 500px;
            padding: 11px;
            border: 1px solid #dfe6dc;
            border-radius: 6px;
            background: #fff;
        }

        @media (max-width: 640px) {
            .details-grid { grid-template-columns: 1fr; }
            .panel { padding: 16px; }
        }
    </style>
@endsection
