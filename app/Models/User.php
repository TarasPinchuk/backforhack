<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as MongoAuthUser;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends MongoAuthUser implements JWTSubject
{
    use Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = ['login', 'password', 'ya_uid', 'role'];
    protected $hidden = ['password'];

     protected $attributes = [
        'role' => 0,
    ];

    public function getRoleAttribute($value)
    {
        return is_null($value) ? 0 : (int) $value;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}