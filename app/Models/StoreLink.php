<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StoreLink extends Model {
 protected $fillable=['store_id','title','url','position','active'];
 protected $casts=['active'=>'boolean'];
 public function store():BelongsTo{return $this->belongsTo(Store::class);}
}