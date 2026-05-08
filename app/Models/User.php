<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    public function getJWTIdentifier(): mixed
    {
        return $this->uuid;
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_uuid', 'uuid');
    }

    public function cart(): HasMany
    {
        return $this->hasMany(Cart::class, 'user_uuid', 'uuid');
    }
}