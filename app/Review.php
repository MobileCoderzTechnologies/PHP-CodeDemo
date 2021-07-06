<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public function reviewedBy(){
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getvideoAttribute($value){
        if($value){
            return asset('/storage/images/'.$value);
        }
    }
}
