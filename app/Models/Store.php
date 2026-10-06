<?php

namespace App\Models;

use App\Enums\VerificationLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'username', 'display_name', 'bio', 'location', 'website', 'category', 'account_type',
        'avatar_path', 'layout', 'theme', 'color_mode', 'seo_title', 'seo_description',
        'currency', 'payment_instructions',
    ];

    protected function casts(): array
    {
        return [
            'theme' => 'array',
            'is_published' => 'boolean',
            'is_suspended' => 'boolean',
            'published_at' => 'datetime',
            'suspended_at' => 'datetime',
            'verification_level' => VerificationLevel::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(StoreLink::class)->orderBy('position')->orderBy('id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    /** Publicly visible: published and not suspended. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('is_suspended', false);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_published && ! $this->is_suspended;
    }

    public function url(): string
    {
        return route('storefront.show', $this->username);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->display_name)) ?: [];
        $letters = array_map(fn ($w) => mb_substr($w, 0, 1), array_slice($words, 0, 2));

        return mb_strtoupper(implode('', $letters)) ?: mb_strtoupper(mb_substr($this->username, 0, 1));
    }

    /** Layout actually rendered (unbuilt presets map to the closest one). */
    public function renderedLayout(): string
    {
        return config('kustore.layout_map.'.$this->layout, 'minimal');
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: $this->display_name.' (@'.$this->username.') · Kustore';
    }

    public function metaDescription(): string
    {
        return $this->seo_description
            ?: ($this->bio ? str($this->bio)->squish()->limit(155)->toString() : 'Links, products and more from '.$this->display_name.' on Kustore.');
    }
}
