@extends('layouts.app')

@section('page-title', __('site.order_details'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.orders.index') }}">@lang('site.orders')</a></li>
    <li class="breadcrumb-item active">@lang('site.show')</li>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="mb-2 mb-md-0">@lang('site.order_details')</h3>
            <div class="order-print-actions d-flex align-items-center flex-wrap">
                <button type="button" class="btn btn-secondary btn-sm mr-2 mb-2" onclick="window.print()">
                    <i class="fa fa-print mr-1"></i> @lang('site.print')
                </button>
                <a href="{{ route('dashboard.orders.edit', $order->id) }}" class="btn btn-warning btn-sm mb-2">
                    <i class="fa fa-pencil mr-1"></i> @lang('site.edit')
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="order-info-card h-100">
                        <span class="order-info-label">@lang('site.customer')</span>
                        <strong class="order-info-value">{{ $order->customer->name }}</strong>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="order-info-card h-100">
                        <span class="order-info-label">@lang('site.unit_price')</span>
                        <strong class="order-info-value">{{ number_format((float) $order->total_amount, 2) }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="order-info-card h-100">
                        <span class="order-info-label">@lang('site.status')</span>
                        <strong class="order-info-value"><span class="badge badge-success order-status-badge">@lang('site.prepared')</span></strong>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="order-info-card h-100">
                        <span class="order-info-label">@lang('site.added_at')</span>
                        <strong class="order-info-value">{{ $order->created_at ? $order->created_at->locale(app()->getLocale())->translatedFormat('j M, Y') : '--' }}</strong>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="order-info-card h-100">
                        <span class="order-info-label">#</span>
                        <strong class="order-info-value">{{ $order->id }}</strong>
                    </div>
                </div>
            </div>

            @if ($order->items->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>@lang('site.products')</th>
                                <th>@lang('site.quantity')</th>
                                <th>@lang('site.unit_price')</th>
                                <th>@lang('site.total')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name ?: '--' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>{{ number_format((float) $item->quantity * (float) $item->unit_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">@lang('site.total')</th>
                                <th>{{ number_format((float) $order->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <h2>@lang('site.no_data_found')</h2>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .order-info-card {
            border: 1px solid #dce6f2;
            border-radius: .9rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            padding: 1rem 1.1rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, .05);
        }

        .order-info-label {
            display: block;
            color: #5f7084;
            font-size: .88rem;
            margin-bottom: .45rem;
        }

        .order-info-value {
            color: #17324d;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .order-status-badge {
            padding: .55rem .9rem;
            font-size: .9rem;
            font-weight: 600;
            border-radius: .45rem;
        }

        @media print {
            .main-header,
            .main-sidebar,
            .main-footer,
            .content-header,
            .preloader,
            .order-print-actions {
                display: none !important;
            }

            body,
            .wrapper,
            .content-wrapper,
            .content,
            .container-fluid {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .content-wrapper {
                min-height: auto !important;
            }

            .card,
            .order-info-card {
                box-shadow: none !important;
            }

            .card {
                border: 0 !important;
            }

            .card-header {
                background: #fff !important;
                color: #000 !important;
                border-bottom: 1px solid #dee2e6 !important;
            }
        }
    </style>
@endpush