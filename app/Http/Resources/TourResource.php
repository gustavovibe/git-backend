<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
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
            'tour_id' => $this->tour_id,
            'tour_name' => $this->tour_name,
            'locale' => $this->locale,
            'language' => $this->language,
            'is_active' => $this->is_active,
            'tour_length_days' => $this->tour_length_days,
            'start_city' => $this->start_city,
            'end_city' => $this->end_city,
            'is_instant_confirmable' => $this->is_instant_confirmable,
            'price_total' => $this->price_total,
            'price_currency' => $this->price_currency,
            'price_promotion' => $this->price_promotion,
            'reviews_count' => $this->reviews_count,
            'ratings_overall' => $this->ratings_overall,
            'ratings_operator' => $this->ratings_operator,
            'description' => $this->description,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'max_group_size' => $this->max_group_size,
            'main_image' => $this->main_image,
            'main_thumbnail' => $this->main_thumbnail,
            'map_image' => $this->map_image,
            'map_thumbnail' => $this->map_thumbnail,
            'type' => $this->type,
            'cities' => $this->cities,
            'countries' => $this->countries,
            'natural_destination' => $this->natural_destination
        ];
    }
}
