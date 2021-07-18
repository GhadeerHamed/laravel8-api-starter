<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'country' => $this->country->name,
            'city' => $this->city->name,
            'sate' => $this->state,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'postal_code' => $this->postal_code,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
