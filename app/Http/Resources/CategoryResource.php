<?php

namespace App\Http\Resources;

use Illuminate\Support\Arr;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'products' => ProductResource::collection($this->whenLoaded('products')),

            $this->merge(Arr::except(parent::toArray($request), [
                'is_active', 'is_home', 'created_at', 'updated_at', 'deleted_at'
            ]))
        ];
    }
}
