<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlightTourResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'flight' => $this->flight,
            'tour' => $this->tour,
            'id_order' => $this->id_order,
        ];
    }
}
