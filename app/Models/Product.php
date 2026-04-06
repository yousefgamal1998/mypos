<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'image',
        'purchase_price',
        'selling_price',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->getTranslationValue('name')
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->getTranslationValue('description')
        );
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => filled($this->image)
                ? asset('storage/'.$this->image)
                : asset('images/product-placeholder.svg')
        );
    }

    protected function profitPercentage(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $purchasePrice = (float) $this->purchase_price;
                $sellingPrice = (float) $this->selling_price;

                if ($purchasePrice <= 0.0) {
                    return 0.0;
                }

                return round((($sellingPrice - $purchasePrice) / $purchasePrice) * 100, 2);
            }
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getTranslation(?string $locale = null): ?ProductTranslation
    {
        $locale ??= app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        $translations = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        return $translations->firstWhere('locale', $locale)
            ?? ($fallbackLocale ? $translations->firstWhere('locale', $fallbackLocale) : null)
            ?? $translations->first();
    }

    public function getTranslationValue(string $field, ?string $locale = null): string
    {
        return (string) ($this->getTranslation($locale)?->{$field} ?? '');
    }

    public function syncTranslations(array $translations): void
    {
        $timestamp = now();

        $rows = collect($translations)
            ->filter(fn ($translation, $locale) => is_string($locale) && is_array($translation))
            ->map(function (array $translation, string $locale) use ($timestamp) {
                return [
                    'product_id' => $this->id,
                    'locale' => $locale,
                    'name' => trim((string) ($translation['name'] ?? '')),
                    'description' => trim((string) ($translation['description'] ?? '')),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })
            ->filter(fn (array $translation) => $translation['name'] !== '' && $translation['description'] !== '')
            ->values()
            ->all();

        if ($rows === []) {
            return;
        }

        $locales = array_column($rows, 'locale');

        $this->translations()->whereNotIn('locale', $locales)->delete();
        $this->translations()->upsert($rows, ['product_id', 'locale'], ['name', 'description', 'updated_at']);
        $this->unsetRelation('translations');
    }

    public function scopeSearchTranslation(Builder $query, string $term): Builder
    {
        return $query->whereHas('translations', function ($translationQuery) use ($term) {
            $translationQuery->where(function ($nestedQuery) use ($term) {
                $nestedQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        });
    }

    public function scopeWithLocaleTranslations(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');
        $locales = array_values(array_unique(array_filter([$locale, $fallbackLocale])));

        return $query->with([
            'translations' => function ($translationQuery) use ($locales) {
                $translationQuery->whereIn('locale', $locales);
            },
        ]);
    }
}
