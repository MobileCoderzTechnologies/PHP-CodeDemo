<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationInvitation extends Model
{
     /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'user_id', 'invited_by', 'status'
    ];
}
