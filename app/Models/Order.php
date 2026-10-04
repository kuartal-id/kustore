<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Order extends Model {
 protected $fillable=['store_id','customer_name','customer_email','amount','currency','status','payment_provider','payment_reference'];
 protected $casts=['amount'=>'decimal:2'];
 public function store():BelongsTo{return $this->belongsTo(Store::class);}
 public function items():HasMany{return $this->hasMany(OrderItem::class);}
}