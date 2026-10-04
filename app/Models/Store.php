<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Store extends Model {
 protected $fillable=['user_id','username','display_name','bio','avatar_url','theme','published'];
 protected $casts=['published'=>'boolean'];
 public function user():BelongsTo{return $this->belongsTo(User::class);}
 public function links():HasMany{return $this->hasMany(StoreLink::class)->orderBy('position');}
 public function products():HasMany{return $this->hasMany(Product::class)->where('active',true)->latest();}
}