@extends('layouts.app')

@section('page-title', __('site.users'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.users.index') }}">@lang('site.users')</a></li>
    <li class="breadcrumb-item active">@lang('site.create')</li>
@endsection

@section('content')

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title mb-0">@lang('site.add')</h3>
        </div>
        <div class="card-body">
            @include('partials._errors')

            <form action="{{ route('dashboard.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="first_name">@lang('site.first_name')</label>
                            <input
                                type="text"
                                name="first_name"
                                id="first_name"
                                class="form-control"
                                value="{{ old('first_name') }}"
                            >
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="last_name">@lang('site.last_name')</label>
                            <input
                                type="text"
                                name="last_name"
                                id="last_name"
                                class="form-control"
                                value="{{ old('last_name') }}"
                            >
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">@lang('site.email')</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        value="{{ old('email') }}"
                    >
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">@lang('site.password')</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation">@lang('site.password_confirmation')</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control"
                            >
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="mb-2" for="avatar">@lang('site.profile_image')</label>
                    <div class="avatar-upload-card">
                        <div class="row align-items-center">
                            <div class="col-auto mb-3 mb-md-0">
                                <div
                                    class="avatar-upload-preview-slot"
                                    data-default-avatar="{{ asset(config('image.default_avatar')) }}"
                                >
                                    <img
                                        id="avatar-preview"
                                        src="{{ asset(config('image.default_avatar')) }}"
                                        alt=""
                                        class="avatar-upload-preview-img"
                                    >
                                </div>
                            </div>
                            <div class="col">
                                <div class="custom-file">
                                    <input
                                        type="file"
                                        name="avatar"
                                        id="avatar"
                                        class="custom-file-input"
                                        accept="image/jpeg,image/png,.jpg,.jpeg,.png"
                                        lang="{{ str_replace('_', '-', app()->getLocale()) }}"
                                    >
                                    <label
                                        class="custom-file-label"
                                        for="avatar"
                                        data-browse="{{ __('site.choose_file') }}"
                                        data-default="{{ __('site.profile_image') }}"
                                    >@lang('site.profile_image')</label>
                                </div>
                                <small class="form-text text-muted mt-2 d-block">@lang('site.image_upload_hint')</small>
                            </div>
                        </div>
                    </div>
                    @error('avatar')
                        <span class="text-danger small d-block mt-2">{{ $message }}</span>
                    @enderror
                </div>

                @include('Dashboard.users._permissions')

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-plus mr-1"></i> @lang('site.add')
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .avatar-upload-card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background: #f8f9fa;
            padding: 1rem 1.25rem;
        }

        .avatar-upload-preview-slot {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 0.375rem;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-upload-preview-slot img.avatar-upload-preview-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-upload-card .custom-file {
            margin-bottom: 0;
        }

        .avatar-upload-card .form-text {
            margin-bottom: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var input = document.getElementById('avatar');
            var preview = document.getElementById('avatar-preview');
            var label = document.querySelector('label.custom-file-label[for="avatar"]');
            var slot = preview ? preview.closest('.avatar-upload-preview-slot') : null;
            var defaultAvatar = slot ? slot.getAttribute('data-default-avatar') : '';

            if (!input || !preview) {
                return;
            }

            function showPreview(src, name) {
                preview.src = src;
                preview.alt = name || '';
            }

            function clearPreview() {
                if (defaultAvatar) {
                    preview.src = defaultAvatar;
                }
                preview.alt = '';
                if (label) {
                    label.textContent = label.getAttribute('data-default') || label.textContent;
                }
            }

            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                if (!file) {
                    clearPreview();
                    return;
                }

                if (!/^image\/(jpeg|png)$/i.test(file.type)) {
                    input.value = '';
                    clearPreview();
                    return;
                }

                if (label) {
                    label.textContent = file.name;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    showPreview(e.target.result, file.name);
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endpush
