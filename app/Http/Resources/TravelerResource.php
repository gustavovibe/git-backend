<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TravelerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'traveler_id' => $this->traveler_id,
            'title' => $this->title,
            'gender' => $this->gender,
            'name' => $this->name,
            'last' => $this->last,
            'birth' => $this->birth,
            'passport' => $this->passport,
            'place' => $this->place,
            'issue' => $this->issue,
            'expire' => $this->expire,
            'mail' => $this->mail,
            'phone' => $this->phone,
            'address' => $this->address,
            'country' => $this->country,
            'lead' => $this->lead,
        ];
    }
}
