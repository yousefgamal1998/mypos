@extends('layouts.app')

@section('page-title', __('site.customers'))

@section('breadcrumb')
    <li class="breadcrumb-item active"><a href="{{ route('dashboard.customers.index') }}">@lang('site.customers')</a></li>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="mb-2 mb-md-0">@lang('site.customers') <small>{{ $customers->total() }}</small></h3>
            </div>
            &nbsp;

            <form action="{{ route('dashboard.customers.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
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
                        <a href="{{ route('dashboard.customers.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> @lang('site.add')
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if ($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('site.name')</th>
                                <th>@lang('site.phone')</th>
                                <th>@lang('site.address')</th>
                                <th>@lang('site.add_order')</th>
                                <th>@lang('site.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $index => $customer)
                                <tr>
                                    <td>{{ $customers->firstItem() + $index }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($customer->address, 120) }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.customers.orders.create', $customer->id) }}" class="btn btn-primary btn-sm">
                                            @lang('site.add_order')
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('dashboard.customers.show', $customer->id) }}" class="btn btn-primary btn-sm">
                                            @lang('site.show')
                                        </a>
                                        <a href="{{ route('dashboard.customers.edit', $customer->id) }}" class="btn btn-info btn-sm">
                                            @lang('site.edit')
                                        </a>
                                        <form
                                            class="form-delete-confirm d-inline-block"
                                            action="{{ route('dashboard.customers.destroy', $customer->id) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                @lang('site.delete')
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
        @if ($customers->hasPages())
            <div class="card-footer clearfix">
                {{ $customers->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.css') }}">
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
