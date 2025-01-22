<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'city_name' => $this->city_name,
            't_city_id' => $this->t_city_id,
            'country' => $this->t_country_id,
            "type"  => "city",
            'country_name' => $this->country->name,
        ];
    }
}
