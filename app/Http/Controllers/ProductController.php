<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductController extends Controller
{
    public function index(): JsonResource
    {
        $data = validator(request()->all(), [
            'per_page' => ['integer'],
            'query' => ['min:3']
        ])->validate();

        $perPage =  $data['per_page'] ??  10;
        $perPage = (int) $perPage;

        $products = Product::query()
            ->when(
                request('query'),
                fn ($builder) => $builder->where(function ($query) use ($data) {
                    $query->where('name', 'like', '%' . $data['query'] . '%')
                        ->orWhere('description', 'like', '%' . $data['query'] . '%');
                })
            )
            ->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function show(Product $product): JsonResource
    {
        return ProductResource::make($product->load('category'));
    }

    public function getRelatedProducts(Product $product): JsonResource
    {
        $limit = request('limit') ?? 5;
        $relatedProducts = Product::query()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->take($limit)->get();

        return ProductResource::collection($relatedProducts);
    }
}
