@extends('layouts.app')

@section('page-title', __('site.products'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.products.index') }}">@lang('site.products')</a></li>
    <li class="breadcrumb-item active">@lang('site.edit')</li>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title mb-0">@lang('site.edit')</h3></div>
        <div class="card-body">
            @include('partials._errors')
            <form action="{{ route('dashboard.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="category_id">@lang('site.categories')</label>
                    <select
                        name="category_id"
                        id="category_id"
                        class="form-control @error('category_id') is-invalid @enderror"
                    >
                        <option value="">@lang('site.select_category')</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                @include('Dashboard.products._translation_fields')
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
                                value="{{ old('purchase_price', $product->purchase_price) }}"
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
                                value="{{ old('selling_price', $product->selling_price) }}"
                            >
                            @error('selling_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="stock">@lang('site.warehouse')</label>
                            <input
                                type="number"
                                min="0"
                                name="stock"
                                id="stock"
                                class="form-control @error('stock') is-invalid @enderror"
                                value="{{ old('stock', $product->stock) }}"
                            >
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-edit mr-1"></i> @lang('site.edit')</button>
                </div>
            </form>
        </div>
    </div>

@endsection


