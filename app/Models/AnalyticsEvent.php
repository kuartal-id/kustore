<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    public const STORE_VIEW = 'store_view';
    public const LINK_CLICK = 'link_click';
    public const PRODUCT_VIEW = 'product_view';

    public const UPDATED_AT = null;

    protected $fillable = ['store_id', 'type', 'store_link_id', 'product_id', 'visitor_hash', 'referrer_host'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
