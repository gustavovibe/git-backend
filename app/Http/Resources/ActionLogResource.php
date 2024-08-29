<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActionLogResource extends JsonResource
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
            'user' => $this->user,
            'action' => $this->action,
            'item' => $this->item,
            'type' => $this->type,
            'date' => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
