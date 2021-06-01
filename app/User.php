<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email_verified_at', 'phone_verified_at', 'created_at', 'updated_at', 'otp', 'device_type', 'jwt_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function addresses(){
        return $this->hasMany(Address::class);
    }


    public function followers()
    {
        return $this->belongsToMany(self::class, 'follows', 'followee_id', 'follower_id');
    }

    public function followees()
    {
        return $this->belongsToMany(self::class, 'follows', 'follower_id', 'followee_id');
    }
}
