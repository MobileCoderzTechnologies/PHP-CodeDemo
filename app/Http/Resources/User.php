<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $profile_privacy = "public";
        if($this->setting){
            $profile_privacy = $this->setting->profile_privacy;
        }

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
            'account_type' => $this->account_type,
            'about_yourself' => $this->about_yourself,
            'profile_pic' => $this->profile_pic,
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
            'profile_privacy' => $profile_privacy,
            'recent_stories_count'  => $this->recentStories->count(),
            'recent_stories'  => $this->recentStories,
        ];
    }
}
