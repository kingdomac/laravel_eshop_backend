<?php

namespace App\Http\Resources;

use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'orders' => $this->whenLoaded('orders'),
            $this->merge(Arr::except(parent::toArray($request), [
                'is_admin', 'created_at', 'updated_at'
            ])),
        ];
    }
}
