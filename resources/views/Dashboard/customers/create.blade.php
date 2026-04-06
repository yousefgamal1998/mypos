@extends('layouts.app')

@section('page-title', __('site.customers'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.customers.index') }}">@lang('site.customers')</a></li>
    <li class="breadcrumb-item active">@lang('site.add')</li>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title mb-0">@lang('site.add')</h3>
        </div>
        <div class="card-body">
            @include('partials._errors')

            <form action="{{ route('dashboard.customers.store') }}" method="POST" novalidate>
                @csrf

                @include('Dashboard.customers._form')

                <div class="form-group mb-0 mt-4 text-right">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-plus mr-1"></i> @lang('site.add')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection