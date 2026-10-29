@extends('rider.layouts.app')

@section('title', 'Rider Dashboard')

@section('content')
<div class="rider-page">
    <div class="page-heading">
        <div>
            <h1>Rider Dashboard</h1>
            <p>Manage your assigned deliveries and update delivery status.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="notice success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="notice error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <span>Total Assigned</span>
            <strong>{{ $counts['total'] }}</strong>
        </div>
        <div class="stat-card">
            <span>Active Deliveries</span>
            <strong>{{ $counts['active'] }}</strong>
        </div>
        <div class="stat-card">
            <span>Completed Deliveries</span>
            <strong>{{ $counts['completed'] }}</strong>
        </div>
    </div>

    <section class="panel">
        <h2>My Deliveries</h2>

        @if($orders->isEmpty())
            <div class="empty-state">
                <h3>No assigned deliveries</h3>
                <p>Your assigned orders will appear here.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Customer</th>
                            <th>Delivery Address</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Delivery Proof</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>
                                    {{ $order->recipient_name ?: $order->customer?->name }}
                                    <br>
                                    <span class="muted">{{ $order->phone }}</span>
                                </td>
                                <td>
                                    {{ $order->address_line }},
                                    {{ $order->barangay }},
                                    {{ $order->municipality }},
                                    {{ $order->province }}
                                    {{ $order->postal_code }}
                                </td>
                                <td>₱{{ number_format((float) $order->total, 2) }}</td>
                                <td>
                                    <span class="status-badge {{ $order->status === 'completed' ? 'done' : 'active' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($order->delivery_proof_path)
                                        <span class="proof-uploaded">Uploaded</span>
                                    @else
                                        <span class="muted">Not uploaded</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn" href="{{ route('rider.orders.show', $order) }}">
                                        View Delivery
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $orders->links() }}
            </div>
        @endif
    </section>
</div>

<style>
.rider-page{max-width:1400px;margin:0 auto;padding:24px;color:#263b2b}
.page-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
.page-heading h1{margin:0;font-size:28px}
.page-heading p,.muted{color:#657267;font-size:13px}
.stats-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:24px}
.stat-card,.panel{background:#fff;border:1px solid #dfe6dc;border-radius:12px;padding:20px}
.stat-card span{display:block;color:#657267;margin-bottom:10px}
.stat-card strong{font-size:28px;color:#245333}
.panel h2{margin:0 0 18px;font-size:20px}
.table-wrap{width:100%;overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:850px}
th,td{text-align:left;padding:13px 12px;border-bottom:1px solid #e5eae2;vertical-align:top}
th{background:#f4f6f0;font-size:12px;text-transform:uppercase}
td{line-height:1.6}
.btn{display:inline-block;background:#245333;color:white;text-decoration:none;padding:9px 12px;border-radius:6px;white-space:nowrap}
.btn:hover{background:#193d25}
.status-badge,.proof-uploaded{display:inline-block;padding:5px 9px;border-radius:20px;font-size:12px;background:#e8f1e5;color:#245333}
.status-badge.done{background:#e2f3e7;color:#176b35}
.empty-state{text-align:center;padding:40px 15px;color:#657267}
.notice{padding:13px 16px;border-radius:8px;margin-bottom:18px}
.notice p{margin:4px 0}
.notice.success{background:#e2f3e7;color:#176b35}
.notice.error{background:#fde8e7;color:#922b25}
.pagination{margin-top:18px}
@media(max-width:700px){.rider-page{padding:14px}.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.stat-card{padding:14px}.stat-card strong{font-size:24px}.page-heading h1{font-size:23px}.table-wrap{-webkit-overflow-scrolling:touch;overscroll-behavior-x:contain}.table-wrap table{min-width:850px}}
@media(max-width:380px){.stats-grid{grid-template-columns:1fr}}
</style>
@endsection
