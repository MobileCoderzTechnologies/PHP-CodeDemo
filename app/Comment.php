<?php

namespace App;
use DB;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $appends = ['is_liked', 'children_comments_count', 'total_likes', 'commented_user', 'sub_comments', 'liked_by'];
    protected $hidden = [
        'story_id', 'user_id', 'parent_comment_id', 'commentedBy', 'childrenComments', 'likes', 'pivot'
    ];

    public function story(){
        return $this->belongsTo(User::class);
    }

    public function commentedBy(){
        return $this->belongsTo(User::class, 'user_id')->select(['id', 'first_name', 'last_name', 'profile_pic']);
    }

    public function childrenComments(){
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    public function likes(){
        return $this->belongsToMany(User::class, 'comment_like', 'comment_id', 'user_id')->select(['user_id', 'first_name', 'last_name', 'profile_pic']);
    }

    public function getsubCommentsAttribute(){
        return $this->childrenComments;
    }

    public function getcommentedUserAttribute(){
        return $this->commentedBy;
    }

    public function getchildrenCommentsCountAttribute(){
        return $this->childrenComments->count();
    }

    public function getlikedByAttribute(){
        return $this->likes;
    }

    public function gettotalLikesAttribute(){
        return $this->likes->count();
    }

    public function getIsLikedAttribute(){
        $isLiked = DB::table('comment_like')->where('comment_id', $this->id)->where('user_id', request()->user->id)->first();
        if($isLiked){
            return 1;
        }

        return 0;
    }
}
