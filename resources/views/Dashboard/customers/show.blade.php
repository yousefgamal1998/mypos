@extends('layouts.app')

@section('page-title', $customer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.customers.index') }}">@lang('site.customers')</a></li>
    <li class="breadcrumb-item active">@lang('site.show')</li>
@endsection

@push('styles')
    <style>
        .customer-show-card{border:1px solid #dce6f2;border-radius:1rem;box-shadow:0 20px 45px rgba(15,23,42,.08);overflow:hidden}
        .customer-show-card .card-header{background:linear-gradient(135deg,#fff 0%,#f5faff 100%);border-bottom:1px solid #e4edf7;padding:1.1rem 1.35rem}
        .customer-show-card .card-body{padding:1.35rem}
        .customer-show-title{font-size:1.1rem;font-weight:700;margin-bottom:0;color:#12324a}
        .customer-show-badge{display:inline-flex;align-items:center;justify-content:center;min-width:2.4rem;padding:.35rem .75rem;border-radius:999px;background:#e8f1ff;color:#1d6fd8;font-weight:700;font-size:.9rem}
        .customer-profile-panel{height:100%;border:1px solid #dce6f2;border-radius:1rem;background:linear-gradient(180deg,#fff 0%,#f8fbff 100%);padding:1.15rem}
        .customer-profile-head{display:flex;align-items:center;gap:1rem;margin-bottom:1rem}
        .customer-profile-avatar{width:4rem;height:4rem;border-radius:1rem;background:linear-gradient(135deg,#1d6fd8 0%,#4f9bff 100%);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1.35rem;font-weight:700;box-shadow:0 16px 28px rgba(29,111,216,.22)}
        .customer-profile-name{font-size:1.3rem;font-weight:700;color:#17324d;margin:0}
        .customer-profile-label{display:inline-flex;align-items:center;padding:.35rem .7rem;border-radius:999px;background:#eef5ff;color:#1d4b77;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.45rem}
        .customer-profile-list{display:grid;gap:.8rem}
        .customer-profile-item{border:1px solid #e1eaf5;border-radius:.9rem;background:#fff;padding:.9rem .95rem}
        .customer-profile-item-label{display:block;color:#607286;font-size:.82rem;margin-bottom:.3rem}
        .customer-profile-item-value{color:#17324d;font-weight:700;word-break:break-word}
        .customer-stats-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.85rem;margin-bottom:1rem}
        .customer-stat-card{border:1px solid #dde7f3;border-radius:.95rem;background:#fff;padding:.95rem 1rem}
        .customer-stat-label{display:block;color:#607286;font-size:.82rem;margin-bottom:.35rem}
        .customer-stat-value{display:block;color:#17324d;font-size:1.2rem;font-weight:700;line-height:1.3}
        .customer-orders-table th,.customer-orders-table td{vertical-align:middle}
        .customer-orders-table thead th{border-top:0;color:#607286;font-size:.82rem;font-weight:700}
        .customer-orders-empty{border:1px dashed #d4deea;border-radius:1rem;background:#fbfdff;color:#607286;padding:1rem;text-align:center}
        @media (max-width:991.98px){.customer-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width:575.98px){.customer-stats-grid{grid-template-columns:1fr}}
    </style>
@endpush

@section('content')
    <div class="card card-primary customer-show-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="customer-show-title mb-2 mb-md-0">{{ $customer->name }}</h3>
                <div class="d-flex flex-wrap">
                    <a href="{{ route('dashboard.customers.orders.create', $customer->id) }}" class="btn btn-primary btn-sm mr-2 mb-2">
                        <i class="fa fa-plus mr-1"></i> @lang('site.add_order')
                    </a>
                    <a href="{{ route('dashboard.customers.edit', $customer->id) }}" class="btn btn-info btn-sm mb-2">
                        <i class="fa fa-edit mr-1"></i> @lang('site.edit')
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="customer-profile-panel">
                        <div class="customer-profile-head">
                            <div class="customer-profile-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customer->name, 0, 1)) }}</div>
                            <div>
                                <span class="customer-profile-label">@lang('site.customer')</span>
                                <h4 class="customer-profile-name">{{ $customer->name }}</h4>
                            </div>
                        </div>
                        <div class="customer-profile-list">
                            <div class="customer-profile-item">
                                <span class="customer-profile-item-label">@lang('site.phone')</span>
                                <div class="customer-profile-item-value">{{ $customer->phone ?: __('site.no_data_found') }}</div>
                            </div>
                            <div class="customer-profile-item">
                                <span class="customer-profile-item-label">@lang('site.alternate_phone')</span>
                                <div class="customer-profile-item-value">{{ $customer->alternate_phone ?: __('site.no_data_found') }}</div>
                            </div>
                            <div class="customer-profile-item">
                                <span class="customer-profile-item-label">@lang('site.address')</span>
                                <div class="customer-profile-item-value">{{ $customer->address ?: __('site.no_data_found') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="customer-stats-grid">
                        <div class="customer-stat-card">
                            <span class="customer-stat-label">@lang('site.orders')</span>
                            <strong class="customer-stat-value">{{ $customerStats['orders_count'] }}</strong>
                        </div>
                        <div class="customer-stat-card">
                            <span class="customer-stat-label">@lang('site.total_spent')</span>
                            <strong class="customer-stat-value">{{ number_format((float) $customerStats['total_spent'], 2) }}</strong>
                        </div>
                        <div class="customer-stat-card">
                            <span class="customer-stat-label">@lang('site.last_order')</span>
                            <strong class="customer-stat-value">
                                {{ $customerStats['last_order_at'] ? \Illuminate\Support\Carbon::parse($customerStats['last_order_at'])->translatedFormat('j M, Y') : __('site.no_data_found') }}
                            </strong>
                        </div>
                    </div>

                    <div class="customer-profile-panel">
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                            <h4 class="customer-show-title mb-2 mb-md-0">@lang('site.recent_orders')</h4>
                            <span class="customer-show-badge">{{ $recentOrders->count() }}</span>
                        </div>

                        @if ($recentOrders->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover customer-orders-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>@lang('site.added_at')</th>
                                            <th>@lang('site.items_count')</th>
                                            <th>@lang('site.total')</th>
                                            <th>@lang('site.action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentOrders as $order)
                                            <tr>
                                                <td>#{{ $order->id }}</td>
                                                <td>{{ optional($order->created_at)->translatedFormat('j F, Y') }}</td>
                                                <td>{{ $order->items_count }}</td>
                                                <td>{{ number_format((float) $order->total_amount, 2) }}</td>
                                                <td>
                                                    <a href="{{ route('dashboard.orders.show', $order->id) }}" class="btn btn-primary btn-sm mr-1 mb-1">
                                                        @lang('site.show')
                                                    </a>
                                                    <a href="{{ route('dashboard.orders.edit', $order->id) }}" class="btn btn-info btn-sm mb-1">
                                                        @lang('site.edit')
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="customer-orders-empty">@lang('site.no_order_history')</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
