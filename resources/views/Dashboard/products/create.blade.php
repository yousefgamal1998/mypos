@extends('layouts.app')

@section('page-title', __('site.products'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.products.index') }}">@lang('site.products')</a></li>
    <li class="breadcrumb-item active">@lang('site.add')</li>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title mb-0">@lang('site.add')</h3>
        </div>
        <div class="card-body">
            @include('partials._errors')

            <form action="{{ route('dashboard.products.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="mb-4">
                    <h4 class="font-weight-bold mb-3">@lang('site.basic_data')</h4>
                    <div class="form-group mb-0">
                        <label for="category_id">@lang('site.categories')</label>
                        <select
                            name="category_id"
                            id="category_id"
                            class="form-control @error('category_id') is-invalid @enderror"
                        >
                            <option value="">@lang('site.select_category')</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        {{-- Field-specific validation message --}}
                        @error('category_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @include('Dashboard.products._translation_fields')

                <div class="mb-0">
                    <h4 class="font-weight-bold mb-3">@lang('site.additional_data')</h4>

                    @include('Dashboard.products._image_field', ['imageUrl' => $product->image_url])


                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="purchase_price">@lang('site.purchase_price')</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="purchase_price"
                                    id="purchase_price"
                                    class="form-control @error('purchase_price') is-invalid @enderror"
                                    value="{{ old('purchase_price') }}"
                                >
                                @error('purchase_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="selling_price">@lang('site.selling_price')</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="selling_price"
                                    id="selling_price"
                                    class="form-control @error('selling_price') is-invalid @enderror"
                                    value="{{ old('selling_price') }}"
                                >
                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="stock">@lang('site.warehouse')</label>
                                <input
                                    type="number"
                                    min="0"
                                    name="stock"
                                    id="stock"
                                    class="form-control @error('stock') is-invalid @enderror"
                                    value="{{ old('stock') }}"
                                >
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0 mt-4 text-right">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-plus mr-1"></i> @lang('site.add')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection







