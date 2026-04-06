@if (isset($customer) && $customer)
    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

    <div class="form-group mb-0">
        <label>@lang('site.customer')</label>
        <input type="text" class="form-control" value="{{ $customer->name }}" disabled>
    </div>
@else
    <div class="form-group mb-0">
        <label for="customer_id">@lang('site.customer')</label>
        <select
            name="customer_id"
            id="customer_id"
            class="form-control @error('customer_id') is-invalid @enderror"
            required
        >
            <option value="">@lang('site.select_customer')</option>
            @foreach ($customers as $listedCustomer)
                <option value="{{ $listedCustomer->id }}" @selected((int) old('customer_id', $order->customer_id) === $listedCustomer->id)>
                    {{ $listedCustomer->name }}
                </option>
            @endforeach
        </select>
        @error('customer_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
@endif