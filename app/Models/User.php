<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
class User extends Authenticatable {
 use Notifiable;
 protected $fillable=['name','email','password','google_id','kuartal_id'];
 protected $hidden=['password','remember_token'];
 protected function casts():array{return ['email_verified_at'=>'datetime','password'=>'hashed'];}
 public function store():HasOne{return $this->hasOne(Store::class);}
}