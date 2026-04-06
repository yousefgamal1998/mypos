{{-- Expects: optional $user with avatar for edit; omit for create (handled in create.blade.php) --}}
<div class="form-group mb-4">
    <label class="mb-2" for="avatar">@lang('site.profile_image')</label>
    @isset($user)
        <div class="mb-2">
            <img src="{{ $user->avatar_url }}" alt="" class="img-circle border" style="width: 72px; height: 72px; object-fit: cover;">
        </div>
    @endisset
    <div class="custom-file">
        <input
            type="file"
            name="avatar"
            id="avatar"
            class="custom-file-input"
            accept="image/jpeg,image/png,.jpg,.jpeg,.png"
            lang="{{ str_replace('_', '-', app()->getLocale()) }}"
        >
        <label class="custom-file-label" for="avatar" data-browse="{{ __('site.choose_file') }}">
            @lang('site.profile_image')
        </label>
    </div>
    <small class="form-text text-muted">@lang('site.image_upload_hint')</small>
    @error('avatar')
        <span class="text-danger small d-block mt-2">{{ $message }}</span>
    @enderror
</div>
