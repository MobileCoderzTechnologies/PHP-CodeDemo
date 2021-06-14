<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $hidden = ['pivot', 'location_id'];
    public function taggedUsers(){
        return $this->belongsToMany(User::class, 'tagged_users', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name']);
    }

    public function customUsers(){
        return $this->belongsToMany(User::class, 'custom_users', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name']);
    }

    public function viewedBy(){
        return $this->belongsToMany(User::class, 'viewed_stories', 'story_id', 'user_id')->select(['user_id', 'first_name', 'last_name']);
    }

    public function location(){
        return $this->belongsTo(LocationInvitation::class, 'location_id', 'id');
    }
}
