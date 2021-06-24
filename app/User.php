<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $appends = ['is_follower', 'total_followers'];

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
        'password', 'remember_token', 'email_verified_at', 'phone_verified_at', 'created_at', 'updated_at', 'otp', 'device_type', 'jwt_token', 'last_login_at', 'followers'
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

    public function setting(){
        return $this->hasOne(Setting::class);
    }


    public function followers()
    {
        return $this->belongsToMany(self::class, 'follows', 'followee_id', 'follower_id')->withPivot('status');
    }

    public function followees()
    {
        return $this->belongsToMany(self::class, 'follows', 'follower_id', 'followee_id')->withPivot('status');
    }

    public function getlogoAttribute($value){
        if($value){
            return asset('/storage/images/'.$value);
        }
    }

    public function getprofilePicAttribute($value){
        if($value){
            return asset('/storage/images/'.$value);
        }
    }

    public function getisFollowerAttribute(){
        if($this->user_id){
            $this->id = $this->user_id;
        }

        $followers = $this->followers;
        foreach($followers as $follower){
            if($follower->id == request()->user->id){
                if($follower->pivot->status=="accepted"){
                    return "yes";
                }

                else if($follower->pivot->status=="pending"){
                    return "pending";
                }
            }
        }

        return "no";
    }

    public function gettotalFollowersAttribute(){

        if($this->user_id){
            $this->id = $this->user_id;
        }

        return $this->followers()->wherePivot('status', 'accepted')->count();
    }
}
