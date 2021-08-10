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
            'is_blocked' => $this->is_blocked,
            'is_online' => $this->is_online,
            'total_followers' => $this->followers()->wherePivot('status', 'accepted')->count(),
            'profile_privacy' => $this->setting->profile_privacy,
            'recent_stories_count'  => $this->recentStories->count(),
            'recent_stories'  => $this->recentStories,
        ];
    }
}
