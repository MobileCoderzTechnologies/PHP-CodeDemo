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
            'total_followers' => $this->followers()->wherePivot('status', 'accepted')->count(),
            //'distance' => $this->distance
        ];
    }
}
