<?php

namespace App\Http\Resources;

use Illuminate\Support\Arr;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'cover' => asset("storage/images/$this->cover"),
            $this->merge(Arr::except(parent::toArray($request), [
                'category_id', 'created_at', 'updated_at', 'deleted_at', 'pivot'
            ])),
            'purchased_price' => $this->when(isset($this->pivot?->price), $this->pivot?->price),
            'quantity' => $this->when(isset($this->pivot?->quantity), $this->pivot?->quantity),
            'category_id' => $this->when($this->whenLoaded('category') instanceof MissingValue, $this->category_id)
        ];
    }
}
