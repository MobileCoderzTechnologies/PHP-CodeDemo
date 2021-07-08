<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
       /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}