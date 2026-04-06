@extends('layouts.app')

@section('page-title', __('site.categories'))

@section('breadcrumb')
    <li class="breadcrumb-item active"><a href="{{ route('dashboard.categories.index') }}">@lang('site.categories')</a></li>
@endsection

@section('content')

    <div class="card card-primary">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="mb-2 mb-md-0">@lang('site.categories') <small>{{ $categories->total() }}</small></h3>
            </div>
            &nbsp;

            <form action="{{ route('dashboard.categories.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ $search }}"
                            placeholder="@lang('site.search')"
                        >
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> @lang('site.search')</button>
                        <a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> @lang('site.add')
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if ($categories->count() > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('site.name')</th>
                            <th>{{ app()->isLocale('ar') ? 'عدد المنتجات' : 'Products Count' }}</th>
                            <th>{{ app()->isLocale('ar') ? 'المنتجات المرتبطة' : 'Related Products' }}</th>
                            <th>@lang('site.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $index => $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $index }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->products_count }}</td>
                                <td>
                                    <a
                                        href="{{ route('dashboard.products.index', ['category_id' => $category->id]) }}"
                                        class="btn btn-primary btn-sm"
                                    >
                                        {{ app()->isLocale('ar') ? 'المنتجات المرتبطة' : 'Related Products' }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('dashboard.categories.edit', $category->id) }}" class="btn btn-info btn-sm">
                                        @lang('site.edit')
                                    </a>

                                    <form
                                        class="form-delete-confirm d-inline-block"
                                        action="{{ route('dashboard.categories.destroy', $category->id) }}"
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
            @else
                <h2>@lang('site.no_data_found')</h2>
            @endif
        </div>
        @if ($categories->hasPages())
            <div class="card-footer clearfix">
                {{ $categories->onEachSide(1)->links() }}
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
