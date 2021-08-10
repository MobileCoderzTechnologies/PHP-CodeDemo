<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Business extends JsonResource
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
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'brief_description' => $this->brief_description,
            'logo' => $this->logo,
            'services' => $this->services,
            'web_url' => $this->web_url,
            'is_follower' => $this->is_follower,
            'is_blocked' => $this->is_blocked,
            'is_online' => $this->is_online,
            'total_followers' => $this->followers()->wherePivot('status', 'accepted')->count(),
            'profile_privacy' => $this->setting->profile_privacy,
            'recent_stories_count'  => $this->recentStories->count(),
            'recent_stories'  => $this->recentStories,
            //'distance' => $this->distance
        ];
    }
}
