<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'sale_price', 'currency', 'type', 'sku',
        'stock_quantity', 'unlimited_stock', 'is_active', 'is_featured', 'requires_shipping', 'position',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock_quantity' => 'integer',
            'unlimited_stock' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'requires_shipping' => 'boolean',
            'type' => ProductType::class,
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderBy('position')->orderByDesc('id');
    }

    public function effectivePrice(): int
    {
        return $this->isOnSale() ? $this->sale_price : $this->price;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function inStock(int $qty = 1): bool
    {
        return $this->unlimited_stock || ($this->stock_quantity ?? 0) >= $qty;
    }

    public function availabilityLabel(): string
    {
        if (! $this->is_active) {
            return 'Unavailable';
        }
        if ($this->unlimited_stock) {
            return 'Available';
        }
        $qty = (int) $this->stock_quantity;

        return match (true) {
            $qty <= 0 => 'Sold out',
            $qty <= 5 => "Only {$qty} left",
            default => 'In stock',
        };
    }

    public function maxPurchasable(): int
    {
        return $this->unlimited_stock ? 99 : max(0, min(99, (int) $this->stock_quantity));
    }

    public function formattedPrice(): string
    {
        return Money::format($this->price, $this->currency);
    }

    public function formattedEffectivePrice(): string
    {
        return Money::format($this->effectivePrice(), $this->currency);
    }

    public function imageUrl(): ?string
    {
        $image = $this->relationLoaded('mainImage') ? $this->mainImage : $this->mainImage()->first();

        return $image ? Storage::disk('public')->url($image->path) : null;
    }

    public function url(): string
    {
        return route('storefront.product', [$this->store->username, $this->slug]);
    }

    public static function uniqueSlugFor(Store $store, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $base = Str::limit($base, 80, '');
        $slug = $base;
        $i = 2;
        while (static::where('store_id', $store->id)->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
