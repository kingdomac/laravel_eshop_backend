<?php

namespace App\Http\Repositries;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepo
{

    public function all(): Collection
    {
        return Category::query()->get();
    }

    public function getHomeCategories(): Collection
    {
        return Category::query()->isHome()->withCount('products')->with(['products' => function ($query) {
            $query->latest()->limit(5)->groupBy(
                Schema::getColumnListing((new Product())->getTable())
            );
        }])->orderBy('id', 'desc')->limit(2)->get();
    }

    public function loadCategoryWithItsProducts(Category $category, int $productNb): Category
    {
        return $category->loadCount('products')->load([
            'products' => function ($query) use ($productNb) {
                $query->orderBy('products.id', 'desc')
                    ->groupBy(Schema::getColumnListing((new Product())->getTable()))
                    ->paginate($productNb);
            }
        ]);
    }
}
