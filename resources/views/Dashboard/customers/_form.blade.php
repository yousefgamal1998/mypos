<div class="form-group">
    <label for="name">@lang('site.name')</label>
    <input
        type="text"
        name="name"
        id="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $customer->name) }}"
        required
    >
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="phone">@lang('site.phone')</label>
            <input
                type="text"
                name="phone"
                id="phone"
                class="form-control @error('phone') is-invalid @enderror"
                value="{{ old('phone', $customer->phone) }}"
                required
            >
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="alternate_phone">{{ app()->isLocale('ar') ? __('site.phone') : __('site.alternate_phone') }}</label>
            <input
                type="text"
                name="alternate_phone"
                id="alternate_phone"
                class="form-control @error('alternate_phone') is-invalid @enderror"
                value="{{ old('alternate_phone', $customer->alternate_phone) }}"
            >
            @error('alternate_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group mb-0">
    <label for="address">@lang('site.address')</label>
    <textarea
        name="address"
        id="address"
        rows="4"
        class="form-control @error('address') is-invalid @enderror"
        required
    >{{ old('address', $customer->address) }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>