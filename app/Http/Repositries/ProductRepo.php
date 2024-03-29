<?php

namespace App\Http\Repositries;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepo
{

    public function getWithFiltrationAndPagination(int $perPage = 10, String $queryString = ""): LengthAwarePaginator
    {
        return Product::query()
            ->when(
                !empty($queryString),
                fn ($builder) => $builder->where(function ($query) use ($queryString) {
                    $query->where('name', 'like', '%' . $queryString . '%')
                        ->orWhere('description', 'like', '%' . $queryString . '%');
                })
            )
            ->paginate($perPage);
    }

    public function getRelatedProducts(Product $relatedToProdcut, ?int $limit = 5): Collection
    {
        return Product::query()
            ->where('id', '!=', $relatedToProdcut->id)
            ->where('category_id', $relatedToProdcut->category_id)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getAllProductsInsideOrder(array $itemsId)
    {
        return Product::query()
            ->select('id', 'price', 'sale_price', 'in_stock')
            ->whereIn('id', $itemsId)
            ->get();
    }
}
