<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryController extends Controller
{
    public function index(): JsonResource
    {
        $categories = Category::query()->get();
        return CategoryResource::collection($categories);
    }

    public function homeCategories(): JsonResource
    {
        $categories = Category::query()->isHome()->withCount('products')->with(['products' => function ($query) {
            $query->latest()->limit(5)->groupBy(
                Schema::getColumnListing((new Product())->getTable())
            );
        }])->orderBy('id', 'desc')->limit(2)->get();
        return CategoryResource::collection($categories);
    }

    public function loadCategory(Category $category): JsonResource
    {
        $perPage = request('per_page') ? (int) request('per_page') : 10;

        $category->loadCount('products')->load([
            'products' => function ($query) use ($perPage) {
                $query->orderBy('products.id', 'desc')
                    ->groupBy(Schema::getColumnListing((new Product())->getTable()))
                    ->paginate($perPage);
            }
        ]);

        $meta = [
            'per_page' => $perPage,
            'current_page' => request('page') ?? 1,
            'total' => $category->products_count,
            'last_page' => ceil($category->products_count / $perPage),
        ];

        return CategoryResource::make($category)->additional(['meta' => $meta]);
    }
}
