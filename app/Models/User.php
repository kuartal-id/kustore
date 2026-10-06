<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'kuartal_id_sub', 'avatar_url', 'email_verified_at', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    public function isKuartalIdUser(): bool
    {
        return $this->kuartal_id_sub !== null;
    }

    /**
     * Local accounts must verify their email before publishing. Kuartal ID
     * accounts are already verified by the identity provider.
     */
    public function canPublishStore(): bool
    {
        return $this->isKuartalIdUser() || $this->hasVerifiedEmail();
    }

    /** Only local (password) accounts receive verification emails. */
    public function sendEmailVerificationNotification(): void
    {
        if (! $this->isKuartalIdUser() && $this->email) {
            parent::sendEmailVerificationNotification();
        }
    }
}
