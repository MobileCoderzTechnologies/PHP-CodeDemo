<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public function getphotoAttribute($value){
        if($value){
            return asset('/storage/images/'.$value);
        }
    }
}
