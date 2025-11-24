<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NaturalDestinationResource extends JsonResource
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
            'destination_id' => $this->destination_id,
            'destination_name' => $this->destination_name,
            'type' => 'natural',
            'name' => $this->destination_name,
            't_natural_id' => $this->t_natural_id,
        ];
    }
}
