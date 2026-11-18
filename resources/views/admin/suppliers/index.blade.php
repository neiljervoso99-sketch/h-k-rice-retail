@extends('admin.layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="page-heading">
    <h1>Suppliers</h1>
    <p>Manage supplier contacts used for purchase orders and receiving.</p>
    <a class="button" href="{{ route('admin.suppliers.create') }}">Add Supplier</a>
    <a class="button" href="{{ route('admin.purchase-orders.index') }}">Purchase Orders</a>
</div>

@if (session('success'))
    <div class="notice success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="notice error">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

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
@endsection
