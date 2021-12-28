<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Arr;

class OrderResource extends JsonResource
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
            'user' => UserResource::make($this->whenLoaded('user')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            //'user_id' => $this->when($this->whenLoaded('user') instanceof MissingValue, $this->user_id),
            $this->merge(Arr::except(parent::toArray($request), [
                'user_id',  'updated_at', 'deleted_at'
            ])),
        ];
    }
}
