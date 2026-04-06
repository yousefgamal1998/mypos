@extends('layouts.app')

@section('page-title', __('site.categories'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.categories.index') }}">@lang('site.categories')</a></li>
    <li class="breadcrumb-item active">@lang('site.add')</li>
@endsection

@section('content')

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title mb-0">@lang('site.add')</h3>
        </div>
        <div class="card-body">
            @include('partials._errors')

            <form action="{{ route('dashboard.categories.store') }}" method="POST">
                @csrf

                @foreach ($locales as $locale)
                    @php
                        $localeLabel = data_get(config("laravellocalization.supportedLocales.{$locale}"), 'native', strtoupper($locale));
                    @endphp

                    <div class="form-group">
                        <label for="translations_{{ $locale }}_name">
                            {{ __('site.name_in_locale', ['locale' => $localeLabel]) }}
                        </label>
                        <input
                            type="text"
                            name="translations[{{ $locale }}][name]"
                            id="translations_{{ $locale }}_name"
                            class="form-control"
                            value="{{ old("translations.{$locale}.name") }}"
                            required
                        >
                    </div>
                @endforeach

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-plus mr-1"></i> @lang('site.add')
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
