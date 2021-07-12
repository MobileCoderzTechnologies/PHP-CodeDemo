<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FutureEvent extends Model
{
    public function getphotoAttribute($value){
        if($value){
            return asset('/storage/images/'.$value);
        }
    }
}
