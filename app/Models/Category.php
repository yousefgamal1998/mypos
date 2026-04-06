<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->getTranslationName()
        );
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getTranslation(?string $locale = null): ?CategoryTranslation
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

    public function getTranslationName(?string $locale = null): string
    {
        return $this->getTranslation($locale)?->name ?? '';
    }

    public function syncTranslations(array $translations): void
    {
        $timestamp = now();

        $rows = collect($translations)
            ->filter(fn ($translation, $locale) => is_string($locale) && is_array($translation))
            ->map(function (array $translation, string $locale) use ($timestamp) {
                return [
                    'category_id' => $this->id,
                    'locale' => $locale,
                    'name' => trim((string) ($translation['name'] ?? '')),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })
            ->filter(fn (array $translation) => $translation['name'] !== '')
            ->values()
            ->all();

        if ($rows === []) {
            return;
        }

        $locales = array_column($rows, 'locale');

        $this->translations()->whereNotIn('locale', $locales)->delete();
        $this->translations()->upsert($rows, ['category_id', 'locale'], ['name', 'updated_at']);
        $this->unsetRelation('translations');
    }

    public function scopeSearchTranslation(Builder $query, string $term): Builder
    {
        return $query->whereHas('translations', function (Builder $translationQuery) use ($term) {
            $translationQuery->where('name', 'like', "%{$term}%");
        });
    }

    public function scopeWithLocaleTranslations(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');
        $locales = array_values(array_unique(array_filter([$locale, $fallbackLocale])));

        return $query->with([
            'translations' => function (HasMany $translationQuery) use ($locales) {
                $translationQuery->whereIn('locale', $locales);
            },
        ]);
    }
}
