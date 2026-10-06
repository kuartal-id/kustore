<?php

namespace App\Models;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'store_id', 'customer_id', 'number', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'notes', 'currency', 'subtotal', 'shipping_total', 'total',
        'payment_provider', 'payment_status', 'fulfillment_status', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_total' => 'integer',
            'total' => 'integer',
            'paid_at' => 'datetime',
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::Paid->value);
    }

    public function formattedTotal(): string
    {
        return Money::format($this->total, $this->currency);
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'KS-'.now()->format('ymd').'-'.strtoupper(Str::random(6));
        } while (static::where('number', $number)->exists());

        return $number;
    }
}
