@extends('layouts.app')

@section('page-title', __('site.users'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.users.index') }}">@lang('site.users')</a></li>
    <li class="breadcrumb-item active">@lang('site.edit')</li>
@endsection

@section('content')

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="">@lang('site.edit')</h3>
        </div>
        <div class="card-body">
            @include('partials._errors')

            <form action="{{ route('dashboard.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                <div class="form-group">
                    <label>@lang('site.first_name')</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}">
                </div>

                <div class="form-group">
                    <label>@lang('site.last_name')</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}">
                </div>

                <div class="form-group">
                    <label>@lang('site.email')</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                </div>

                <div class="form-group">
                    <label>@lang('site.password')</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="form-group">
                    <label>@lang('site.password_confirmation')</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                @include('Dashboard.users._avatar_field', ['user' => $user])

                @include('Dashboard.users._permissions')

                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-edit"></i> @lang('site.edit')</button>
                </div>

            </form>

        </div>
    </div>

@endsection
