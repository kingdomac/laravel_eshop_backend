<?php

namespace App\Http\Controllers;

use App\Http\Repositries\ProductRepo;
use App\Http\Requests\Product\ListRequest;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;
//tt
class ProductController extends Controller
{
    public function __construct(protected ProductRepo $productRepo)
    {
    }
    public function index(ListRequest $request): JsonResource
    {
        $data = $request->validated();
        $perPage = $data['per_page'] ?? 10;
        $queryString = $data['query'] ?? '';
        $products = $this->productRepo->getWithFiltrationAndPagination(
            perPage: $perPage,
            queryString: $queryString
        );

        return ProductResource::collection($products);
    }

    public function show(Product $product): JsonResource
    {
        return ProductResource::make($product->load('category'));
    }

    public function getRelatedProducts(Product $product): JsonResource
    {
        $relatedProducts = $this->productRepo->getRelatedProducts($product, request('limit'));

        return ProductResource::collection($relatedProducts);
    }
}
