<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    protected $appends = ['is_follower', 'total_followers', 'is_blocked', 'blocked_me'];

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
        'password', 'remember_token', 'email_verified_at', 'phone_verified_at', 'created_at', 'updated_at', 'otp', 'device_type', 'jwt_token', 'last_login_at', 'followers', 'blockedTo', 'blockedBy'
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

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_user', 'user_id', 'category_id');
    }


    public function followers()
    {
        return $this->belongsToMany(self::class, 'follows', 'followee_id', 'follower_id')->withPivot('status');
    }

    public function followees()
    {
        return $this->belongsToMany(self::class, 'follows', 'follower_id', 'followee_id')->withPivot('status');
    }

    public function notifyTo()
    {
        return $this->belongsToMany(self::class, 'story_notifications', 'notify_by', 'notify_to');
    }

    public function notifyBy()
    {
        return $this->belongsToMany(self::class, 'story_notifications', 'notify_to', 'notify_by');
    }

    public function blockedTo()
    {
        return $this->belongsToMany(self::class, 'blocked_users', 'blocked_by', 'blocked_to');
    }

    public function blockedBy()
    {
        return $this->belongsToMany(self::class, 'blocked_users', 'blocked_to', 'blocked_by');
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

    public function getisBlockedAttribute(){
        if($this->user_id){
            $this->id = $this->user_id;
        }
        $blockedToUsers = $this->blockedBy;
        foreach($blockedToUsers as $blockedToUser){
            if($blockedToUser->id == request()->user->id){
              return 1;
            }
        }

        return 0;
    }

    public function getBlockedMeAttribute(){
        if($this->user_id){
            $this->id = $this->user_id;
        }
        $blockedToUsers = $this->blockedTo;
        foreach($blockedToUsers as $blockedToUser){
            if($blockedToUser->id == request()->user->id){
              return 1;
            }
        }

        return 0;
    }

    public function gettotalFollowersAttribute(){

        if($this->user_id){
            $this->id = $this->user_id;
        }

        return $this->followers()->wherePivot('status', 'accepted')->count();
    }

    public function recentStories(){
        return $this->hasMany(Story::class)->where('created_at', '>=', Carbon::now()->subDay());
    }

    public function unreadNotifications(){
        return $this->hasMany(Notification::class)->where('is_read', 0);
    }
}
