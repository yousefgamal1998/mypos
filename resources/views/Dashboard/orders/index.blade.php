@extends('layouts.app')

@section('page-title', __('site.orders'))

@section('breadcrumb')
    <li class="breadcrumb-item active"><a href="{{ route('dashboard.orders.index') }}">@lang('site.orders')</a></li>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="mb-2 mb-md-0">@lang('site.orders') <small>{{ $orders->total() }}</small></h3>
            </div>
            &nbsp;

            <form action="{{ route('dashboard.orders.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ $search }}"
                            placeholder="@lang('site.search')"
                            autocomplete="off"
                        >
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> @lang('site.search')</button>
                        <a href="{{ route('dashboard.orders.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> @lang('site.add')
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if ($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>@lang('site.customer')</th>
                                <th>@lang('site.unit_price')</th>
                                <th>@lang('site.status')</th>
                                <th>@lang('site.added_at')</th>
                                <th>@lang('site.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->customer->name }}</td>
                                    <td class="order-price-cell">{{ number_format((float) $order->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-success order-status-badge">@lang('site.prepared')</span>
                                    </td>
                                    <td class="text-muted order-date-cell">
                                        {{ $order->created_at ? $order->created_at->locale(app()->getLocale())->translatedFormat('j M, Y') : '--' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('dashboard.orders.show', $order->id) }}" class="btn btn-primary btn-sm mr-1 mb-1">
                                            <i class="fa fa-list mr-1"></i> @lang('site.show')
                                        </a>
                                        <a href="{{ route('dashboard.orders.edit', $order->id) }}" class="btn btn-warning btn-sm mr-1 mb-1">
                                            <i class="fa fa-pencil mr-1"></i> @lang('site.edit')
                                        </a>
                                        <form action="{{ route('dashboard.orders.destroy', $order->id) }}" method="POST" class="d-inline-block form-delete-confirm mb-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash mr-1"></i> @lang('site.delete')
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <h2>@lang('site.no_data_found')</h2>
            @endif
        </div>
        @if ($orders->hasPages())
            <div class="card-footer clearfix">
                {{ $orders->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.css') }}">
    <style>
        .order-status-badge {
            padding: .55rem .9rem;
            font-size: .9rem;
            font-weight: 600;
            border-radius: .45rem;
        }

        .order-price-cell {
            font-weight: 700;
            color: #17324d;
            white-space: nowrap;
        }

        .order-date-cell {
            white-space: nowrap;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.all.js') }}"></script>
    <script>
        document.querySelectorAll('form.form-delete-confirm').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                Swal.fire({
                    text: @json(__('site.confirm_delete')),
                    icon: 'warning',
                    showCancelButton: true,
                    focusCancel: true,
                    confirmButtonText: @json(__('site.delete')),
                    cancelButtonText: @json(__('site.cancel')),
                    confirmButtonColor: '#c82333',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    rtl: {{ app()->getLocale() === 'ar' ? 'true' : 'false' }},
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush