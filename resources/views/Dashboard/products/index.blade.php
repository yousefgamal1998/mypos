@extends('layouts.app')

@section('page-title', __('site.products'))

@section('breadcrumb')
    <li class="breadcrumb-item active"><a href="{{ route('dashboard.products.index') }}">@lang('site.products')</a></li>
@endsection

@push('styles')
    .products-table-image {
        width: 120px;
        max-width: 100%;
        height: 72px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #d7dee7;
        background: #f8fafc;
        display: block;
    }

    .products-table-image-cell {
        width: 140px;
    }

    .products-filter-control {
        height: calc(2.25rem + 4px);
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .products-filter-control::placeholder {
        font-size: 0.95rem;
    }

    .products-filter-select {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
@endpush

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="mb-2 mb-md-0">@lang('site.products') <small>{{ $products->total() }}</small></h3>
            </div>
            &nbsp;

            <form action="{{ route('dashboard.products.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <input
                            type="text"
                            name="search"
                            class="form-control products-filter-control"
                            value="{{ $search }}"
                            placeholder="@lang('site.search')"
                        >
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <select name="category_id" class="form-control products-filter-control products-filter-select">
                            <option value="">@lang('site.all_categories')</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) optional($selectedCategory)->id === (int) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> @lang('site.search')</button>
                        <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> @lang('site.add')
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if ($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('site.name')</th>
                                <th>@lang('site.description')</th>
                                <th>@lang('site.image')</th>
                                <th>@lang('site.purchase_price')</th>
                                <th>@lang('site.selling_price')</th>
                                <th>@lang('site.profit')</th>
                                <th>@lang('site.warehouse')</th>
                                <th>@lang('site.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $index => $product)
                                <tr>
                                    <td>{{ $products->firstItem() + $index }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 120) }}</td>
                                    <td class="products-table-image-cell">
                                        <img
                                            src="{{ $product->image_url }}"
                                            alt="{{ $product->name }}"
                                            class="products-table-image"
                                        >
                                    </td>
                                    <td>{{ $product->purchase_price }}</td>
                                    <td>{{ $product->selling_price }}</td>
                                    <td>{{ number_format($product->profit_percentage, 2) }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.products.edit', $product->id) }}" class="btn btn-info btn-sm">@lang('site.edit')</a>
                                        <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">@lang('site.delete')</button>
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
        @if ($products->hasPages())
            <div class="card-footer clearfix">
                {{ $products->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection