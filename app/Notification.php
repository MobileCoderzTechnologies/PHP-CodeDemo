<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $hidden = ['user_id', 'requested_by', 'accepted_by'];

    public function requestedBy(){
        return $this->belongsTo(User::class, 'requested_by')->select(['id', 'first_name', 'last_name', 'profile_pic']);
    }

    public function acceptedBy(){
        return $this->belongsTo(User::class, 'accepted_by')->select(['id', 'first_name', 'last_name', 'profile_pic']);
    }
}
