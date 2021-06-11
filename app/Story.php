<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    public function taggedUsers(){
        return $this->belongsToMany(User::class, 'tagged_users', 'story_id', 'user_id');
    }

    public function customUsers(){
        return $this->belongsToMany(User::class, 'custom_users', 'story_id', 'user_id');
    }

    public function viewedBy(){
        return $this->belongsToMany(User::class, 'viewed_stories', 'story_id', 'user_id');
    }
}
