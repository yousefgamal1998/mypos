<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
    ];

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $searchQuery) use ($term) {
            $searchQuery->whereHas('customer', function (Builder $customerQuery) use ($term) {
                $customerQuery->search($term);
            });

            if (ctype_digit($term)) {
                $searchQuery->orWhere('id', (int) $term);
            }
        });
    }

    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes): float {
                if (array_key_exists('total_amount', $attributes)) {
                    return round((float) $attributes['total_amount'], 2);
                }

                if ($this->relationLoaded('items')) {
                    return round((float) $this->items->sum(
                        fn (OrderItem $item): float => ((int) $item->quantity) * ((float) $item->unit_price)
                    ), 2);
                }

                return round((float) $this->items()
                    ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total_amount')
                    ->value('total_amount'), 2);
            }
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}