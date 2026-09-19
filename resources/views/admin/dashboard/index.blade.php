@extends('admin.layouts.app')
@section('title', 'Dashboard ERP')
@section('content')
@php
    $dashboardCardIcons = [
        'iconoir-shop' => 'shopping-bag',
        'iconoir-user' => 'user',
        'iconoir-box-iso' => 'box',
        'iconoir-cart' => 'shopping-cart',
        'iconoir-community' => 'users',
        'iconoir-check-circle' => 'check-circle',
        'iconoir-calendar-minus' => 'calendar',
        'iconoir-receive-dollars' => 'dollar-sign',
    ];
@endphp
<div class="row">
    @foreach($stats as $stat)
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-{{ $stat['color'] }}-subtle text-{{ $stat['color'] }} thumb-xl rounded-circle d-flex align-items-center justify-content-center">
                            <i class="admin-dashboard-card-icon" data-feather="{{ $dashboardCardIcons[$stat['icon']] ?? 'circle' }}"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">{{ $stat['label'] }}</p>
                            <h3 class="mb-1">{{ number_format((float) $stat['value']) }}</h3>
                            <small class="text-muted">{{ $stat['trend'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Today Sales</p><h4 class="mb-0">Rs. {{ number_format((float) $todaySales, 2) }}</h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Month Sales</p><h4 class="mb-0">Rs. {{ number_format((float) $monthSales, 2) }}</h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Pending Dealers</p><h4 class="mb-0">{{ $pendingDealers->count() }}</h4></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Low Stock Alerts</p><h4 class="mb-0">{{ $lowStock->count() }}</h4></div></div></div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col"><h4 class="card-title">Recent Sale Orders</h4></div>
                    <div class="col-auto"><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Order</th><th>Channel</th><th>Party</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->order_no }}</a><small class="d-block text-muted">{{ $order->created_at->format('d-m-Y') }}</small></td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ strtoupper($order->order_type) }}</span></td>
                                    <td>{{ $order->dealer?->dealerProfile?->firm_name ?? $order->customer?->name ?? '-' }}</td>
                                    <td>Rs. {{ number_format((float) $order->grand_total, 2) }}</td>
                                    <td><span class="badge bg-{{ in_array($order->status, ['approved', 'delivered']) ? 'success' : 'warning' }}-subtle text-{{ in_array($order->status, ['approved', 'delivered']) ? 'success' : 'warning' }}">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">No sale orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col"><h4 class="card-title">Pending Dealer Sale Orders</h4></div>
                            <div class="col-auto"><a href="{{ route('admin.orders.index', ['type' => 'dealer']) }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Order</th><th>Dealer</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($pendingDealerOrders as $order)
                                        <tr>
                                            <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->order_no }}</a><small class="d-block text-muted">{{ $order->salesman?->name ?? '-' }}</small></td>
                                            <td>{{ $order->dealer?->dealerProfile?->firm_name ?? $order->dealer?->name ?? '-' }}</td>
                                            <td>Rs. {{ number_format((float) $order->grand_total, 2) }}</td>
                                            <td><span class="badge bg-warning-subtle text-warning">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No pending dealer sale orders.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col"><h4 class="card-title">Pending Customer Sale Orders</h4></div>
                            <div class="col-auto"><a href="{{ route('admin.orders.index', ['type' => 'customer']) }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($pendingCustomerOrders as $order)
                                        <tr>
                                            <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->order_no }}</a><small class="d-block text-muted">{{ $order->created_at->format('d-m-Y') }}</small></td>
                                            <td>{{ $order->customer?->name ?? '-' }}</td>
                                            <td>Rs. {{ number_format((float) $order->grand_total, 2) }}</td>
                                            <td><span class="badge bg-warning-subtle text-warning">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No pending customer sale orders.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title">ERP Action Centre</h4></div>
            <div class="card-body">
                <a href="{{ route('admin.dealers.index', ['status' => 'pending_approval']) }}" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-shop me-2 text-primary"></i>Dealer approvals</span><strong>{{ $pendingDealers->count() }}</strong></a>
                <a href="{{ route('admin.orders.index', ['status' => 'admin_review']) }}" class="d-flex justify-content-between border-bottom py-3 text-body"><span><i class="iconoir-cart me-2 text-success"></i>Admin review orders</span><strong>{{ $stats[3]['value'] }}</strong></a>
                <a href="{{ route('admin.products.index') }}" class="d-flex justify-content-between py-3 text-body"><span><i class="iconoir-box me-2 text-info"></i>Products</span><strong>{{ $stats[2]['value'] }}</strong></a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4 class="card-title">Low Stock</h4></div>
            <div class="card-body pt-0">
                @forelse($lowStock as $stock)
                    <div class="d-flex justify-content-between py-2 border-bottom"><div><strong>{{ $stock->product?->name }}</strong><small class="d-block text-muted">{{ $stock->warehouse?->name }} - {{ $stock->batch_no }}</small></div><span class="badge bg-danger-subtle text-danger align-self-center">{{ $stock->quantity }}</span></div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">No low-stock alerts.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection