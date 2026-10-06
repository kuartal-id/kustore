<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreLink extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'url', 'icon', 'is_visible', 'position'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
