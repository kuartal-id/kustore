<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Product extends Model {
 protected $fillable=['store_id','name','slug','description','type','price','currency','image_url','active','inventory'];
 protected $casts=['price'=>'decimal:2','active'=>'boolean'];
 public function store():BelongsTo{return $this->belongsTo(Store::class);}
 public function orderItems():HasMany{return $this->hasMany(OrderItem::class);}
}