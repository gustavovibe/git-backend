<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'booking_id' => $this->booking_id,
            'departure' => $this->departure,
            'start' => $this->start,
            'arrival' => $this->arrival,
            'end' => $this->end,
            'duration' => $this->duration,
            'tour_length' => $this->tour_length,
            'tour_name' => $this->tour_name,
            'tour_id' => $this->tour_id,
            'operator' => $this->operator,
            'start_city' => $this->start_city,
            'end_city' => $this->end_city,
            'booking_status' => $this->booking_status,
            'tourradar_id' => $this->tourradar_id,
            'tourradar_status' => $this->tourradar_status,
            'tourradar_reason' => $this->tourradar_reason,
            'tourradar_text' => $this->tourradar_text,
            'duffel_id' => $this->duffel_id,
            'origin' => $this->origin,
            'f_destination' => $this->f_destination,
            'f_return' => $this->f_return,
            'f_duration' => $this->f_duration,
            'destination_stops' => $this->destination_stops,
            'return_stops' => $this->return_stops,
            'total_stops' => $this->total_stops,
            'destination_carrier' => $this->destination_carrier,
            'return_carrier' => $this->return_carrier,
            'checked_bags' => $this->booking_id,
            'travelers_number' => $this->travelers_number,
            'reference' => $this->reference,
            'currency' => $this->currency,
            'paid' => $this->paid,
            'p_flight' => $this->p_flight,
            'p_tour' => $this->p_tour,
            'commission_value_tour' => $this->commission_value_tour,
            'discounted' => $this->discounted,
            'promo' => $this->promo,
            'user_id' => $this->user_id,
            'whole_trip' => $this->whole_trip,
            'channel' => $this->channel,
            'payment_method' => $this->payment_method,
            'payment_id' => $this->payment_id,
            'medium' => $this->medium,
            'gender' => $this->gender,
            'age_group' => $this->age_group,
            'group_size' => $this->group_size,
            'country' => $this->country,
            'carrier' => $this->carrier,
            'travelers' => TravelerResource::collection($this->travelers),
            'user' => $this->user,
            'flightTour' => $this->flightTour,
            'tour' => $this->tour,
        ];
    }
}
