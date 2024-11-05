<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TourNaturalDestinationResource extends JsonResource
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
            'natural_destination' => $this->natural_destination,
            't_natural_id' => $this->t_natural_id,
            'tour' => $this->tour,
            "type"  => "natural_destination"
        ];
    }
}
