<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class Personal extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

       
        //return parent::toArray($request);

        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'email' => $this->email, 
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'job' => $this->job,
            'dob' => $this->dob,
            'about_yourself' => $this->about_yourself,
            'profile_pic' => $this->profile_pic,
            'is_follower' => $this->is_follower,
            'total_followers' => $this->followers->count()
        ];
    }
}
