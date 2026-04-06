@once
    @push('styles')
        .product-description-editor + .ck-editor .ck-editor__editable_inline {
            min-height: 260px;
        }

        .product-description-editor + .ck-editor {
            display: block;
        }

        .product-description-editor.is-invalid + .ck-editor .ck-editor__editable_inline {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
        }
    @endpush

    @push('scripts')
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.ClassicEditor) {
                    return;
                }

                document.querySelectorAll('.product-description-editor').forEach((element) => {
                    if (!element || element.dataset.ckeditorInitialized === '1') {
                        return;
                    }

                    element.dataset.ckeditorInitialized = '1';

                    ClassicEditor.create(element, {
                        language: element.id.includes('_ar_') ? 'ar' : 'en'
                    })
                        .then((editor) => {
                            if (element.id.includes('_ar_')) {
                                editor.editing.view.change((writer) => {
                                    writer.setStyle('direction', 'rtl', editor.editing.view.document.getRoot());
                                    writer.setStyle('text-align', 'right', editor.editing.view.document.getRoot());
                                });
                            }
                        })
                        .catch((error) => {
                            console.error('CKEditor initialization failed:', error);
                            element.dataset.ckeditorInitialized = '0';
                        });
                });
            });
        </script>
    @endpush
@endonce

@foreach ($locales as $locale)
    @php
        $localeLabelKey = "site.locale_{$locale}";
        $localeLabel = __($localeLabelKey);
        $nameField = "translations.{$locale}.name";
        $descriptionField = "translations.{$locale}.description";

        if ($localeLabel === $localeLabelKey) {
            $localeLabel = strtoupper($locale);
        }
    @endphp

    <div class="card card-outline card-secondary mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">{{ __('site.data_in_locale', ['locale' => $localeLabel]) }}</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="translations_{{ $locale }}_name">
                    {{ __('site.name_in_locale', ['locale' => $localeLabel]) }}
                </label>
                <input
                    type="text"
                    name="translations[{{ $locale }}][name]"
                    id="translations_{{ $locale }}_name"
                    class="form-control @error($nameField) is-invalid @enderror"
                    value="{{ old("translations.{$locale}.name", $product->getTranslationValue('name', $locale)) }}"
                >
                @error($nameField)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-0">
                <label for="translations_{{ $locale }}_description">
                    {{ __('site.description_in_locale', ['locale' => $localeLabel]) }}
                </label>
                <textarea
                    name="translations[{{ $locale }}][description]"
                    id="translations_{{ $locale }}_description"
                    class="form-control product-description-editor @error($descriptionField) is-invalid @enderror"
                    rows="4"
                >{{ old("translations.{$locale}.description", $product->getTranslationValue('description', $locale)) }}</textarea>
                @error($descriptionField)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
@endforeach





