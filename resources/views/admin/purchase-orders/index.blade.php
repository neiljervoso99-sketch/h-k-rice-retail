@extends('admin.layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<style>
    .purchase-order-delete-form { display: inline; }
    .purchase-order-delete-button { background: #a52b24; border-color: #a52b24; color: #fff; }
</style>
<div class="page-heading">
    <h1>Purchasing &amp; Suppliers</h1>
    <p>Manage purchase orders, deliveries, and supplier contacts.</p>
    <a class="button" href="{{ route('admin.purchase-orders.create') }}">Create Purchase Order</a>
    <a class="button" href="{{ route('admin.suppliers.create') }}">Add Supplier</a>
    @if ($purchaseOrders->total() > 0 || $hasSalesOrders)
        <form class="purchase-order-delete-form" method="POST" action="{{ route('admin.purchase-orders.destroy-all') }}" onsubmit="if (!confirm('Are you sure you want to delete?')) return false; return confirm('Please print your transactions before deleting. Have you printed the records? Press OK to delete or Cancel to go back.');">
            @csrf
            @method('DELETE')
            <button class="button purchase-order-delete-button" type="submit">Delete All Transactions</button>
        </form>
    @endif
</div>

@if (session('success'))
    <div class="notice success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="notice error">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<section class="panel">
    <h2>Purchase Orders</h2>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Items</th>
                <th>Receipts</th>
                <th>Expected</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchaseOrders as $purchaseOrder)
                <tr>
                    <td>{{ $purchaseOrder->order_number }}</td>
                    <td>{{ $purchaseOrder->supplier->name }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($purchaseOrder->status)) }}</td>
                    <td>{{ $purchaseOrder->items_count }}</td>
                    <td>{{ $purchaseOrder->receipts_count }}</td>
                    <td>{{ $purchaseOrder->expected_at?->format('M j, Y') ?: '—' }}</td>
                    <td><a class="button" href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}">View / Receive</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No purchase orders have been created.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $purchaseOrders->links() }}
</section>

<section class="panel">
    <h2>Suppliers</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Contact</th>
                    <th>Email / Phone</th>
                    <th>Status</th>
                    <th>Purchase Orders</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td>
                            <strong>{{ $supplier->name }}</strong>
                            @if ($supplier->address)<div>{{ $supplier->address }}</div>@endif
                        </td>
                        <td>{{ $supplier->contact_person ?: '—' }}</td>
                        <td>
                            {{ $supplier->email ?: '—' }}
                            @if ($supplier->phone)<div>{{ $supplier->phone }}</div>@endif
                        </td>
                        <td>{{ $supplier->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>{{ $supplier->purchase_orders_count }}</td>
                        <td>
                            <a class="button" href="{{ route('admin.suppliers.edit', $supplier) }}">Edit</a>
                            @if ($supplier->purchase_orders_count === 0)
                                <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" style="display:inline" onsubmit="return confirm('Delete this supplier?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button button-danger" type="submit">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No suppliers have been added.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $suppliers->links() }}
</section>
@endsection
