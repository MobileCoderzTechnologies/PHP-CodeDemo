<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Story extends Model
{
    protected $hidden = ['pivot', 'location_id', 'updated_at'];
    protected $appends = ['is_viewed', 'is_liked'];
    public function taggedUsers(){
        return $this->belongsToMany(User::class, 'tagged_users', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name']);
    }

    public function customUsers(){
        return $this->belongsToMany(User::class, 'custom_users', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name']);
    }

    public function viewedBy(){
        return $this->belongsToMany(User::class, 'viewed_stories', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name', 'is_liked']);
    }

    public function location(){
        return $this->belongsTo(LocationInvitation::class, 'location_id', 'id');
    }
    
    public function getIsViewedAttribute(){
        $isViewd = DB::table('viewed_stories')->where('story_id', $this->id)->where('user_id', request()->user->id)->first();
        if($isViewd){
            return true;
        }

        return false;
    }

    public function getIsLikedAttribute(){
        $isLiked = DB::table('viewed_stories')->where('story_id', $this->id)->where('user_id', request()->user->id)->where('is_liked', true)->first();
        if($isLiked){
            return true;
        }

        return false;
    }

    public function getfileAttribute($value){
        if($value){
            return asset('/storage/images/'.$value);
        }
    }
}
