@php
    $previewImageUrl = filled($imageUrl ?? null) ? $imageUrl : asset('images/product-placeholder.svg');
@endphp

@once
    @push('styles')
        .product-image-preview {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border: 1px solid #d7dee7;
            background: #f8fafc;
        }
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.custom-file-input').forEach((input) => {
                    input.addEventListener('change', () => {
                        const label = input.nextElementSibling;
                        const file = input.files && input.files[0];
                        const preview = document.querySelector(input.dataset.previewTarget);

                        if (label) {
                            label.textContent = file ? file.name : label.dataset.defaultLabel || label.textContent;
                        }

                        if (preview && file) {
                            preview.src = URL.createObjectURL(file);
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce

<div class="form-group">
    <label for="image">@lang('site.image')</label>

    <div class="card card-outline card-secondary mb-0 @error('image') border-danger @enderror">
        <div class="card-body py-3">
            <div class="mb-3 text-center text-md-left">
                <img
                    src="{{ $previewImageUrl }}"
                    alt="Product preview"
                    id="product-image-preview"
                    class="img-thumbnail product-image-preview"
                >
            </div>

            <div class="custom-file">
                <input
                    type="file"
                    name="image"
                    id="image"
                    class="custom-file-input @error('image') is-invalid @enderror"
                    accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                    data-preview-target="#product-image-preview"
                >
                <label class="custom-file-label" for="image" data-default-label="@lang('site.choose_file')">@lang('site.choose_file')</label>

                @error('image')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <small class="form-text text-muted mt-2">@lang('site.image_upload_hint')</small>
        </div>
    </div>
</div>
