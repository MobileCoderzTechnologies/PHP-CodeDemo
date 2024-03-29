<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Story extends Model
{
    protected $hidden = ['pivot', 'location_id', 'updated_at'];
    protected $appends = ['is_viewed', 'is_liked', 'total_likes', 'total_views', 'total_comments'];

    public function taggedUsers(){
        return $this->belongsToMany(User::class, 'tagged_users', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name', 'profile_pic', 'business_name', 'logo', 'account_type']);
    }

    public function customUsers(){
        return $this->belongsToMany(User::class, 'custom_users', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name', 'business_name', 'logo', 'account_type']);
    }

    public function viewedBy(){
        return $this->belongsToMany(User::class, 'viewed_stories', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name', 'profile_pic', 'is_liked', 'business_name', 'logo', 'account_type']);
    }

    public function storyAddedBy(){
        return $this->belongsTo(User::class, 'user_id')->select(['id', 'first_name', 'last_name', 'profile_pic', 'business_name', 'logo', 'account_type']);
    }
    
    public function getIsViewedAttribute(){
        $isViewd = DB::table('viewed_stories')->where('story_id', $this->id)->where('user_id', request()->user->id)->first();
        if($isViewd){
            return 1;
        }

        return 0;
    }

    public function getIsLikedAttribute(){
        $isLiked = DB::table('viewed_stories')->where('story_id', $this->id)->where('user_id', request()->user->id)->where('is_liked', true)->first();
        if($isLiked){
            return 1;
        }

        return 0;
    }

    public function getfileAttribute($value){
        if($value){
            return asset('/storage/images/'.$value);
        }
    }

    public function getbusinessImageAttribute($value){
        if($value){
            return asset('/storage/images/'.$value);
        }
    }

    public function getTotalLikesAttribute(){
        return $this->viewedBy()->where('is_liked', true)->count();
    }

    public function getTotalViewsAttribute(){
        return $this->viewedBy()->count();
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function getTotalCommentsAttribute(){
        return $this->comments()->count();
    }
}
