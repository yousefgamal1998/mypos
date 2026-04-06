@extends('layouts.app')

@section('page-title', __('site.orders'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.orders.index') }}">@lang('site.orders')</a></li>
    <li class="breadcrumb-item active">@lang('site.edit')</li>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title mb-0">@lang('site.edit')</h3>
        </div>
        <div class="card-body">
            @include('partials._errors')

            <form action="{{ route('dashboard.orders.update', $order->id) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                @include('Dashboard.orders._form')

                <div class="form-group mb-0 mt-4 text-right">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-edit mr-1"></i> @lang('site.edit')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection