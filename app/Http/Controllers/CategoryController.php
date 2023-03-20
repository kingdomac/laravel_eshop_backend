<?php

namespace App\Http\Controllers;

use App\Http\Repositries\CategoryRepo;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryController extends Controller
{
    public function __construct(protected CategoryRepo $categoryRepo)
    {
    }
    public function index(): JsonResource
    {
        $categories = $this->categoryRepo->all();
        return CategoryResource::collection($categories);
    }

    public function homeCategories(): JsonResource
    {
        $categories = $this->categoryRepo->getHomeCategories();
        return CategoryResource::collection($categories);
    }

    public function loadCategory(Category $category): JsonResource
    {
        $perPage = request('per_page') ? (int) request('per_page') : 10;

        $categoryWithProducts = $this->categoryRepo->loadCategoryWithItsProducts($category, $perPage);

        $meta = [
            'per_page' => $perPage,
            'current_page' => request('page') ?? 1,
            'total' => $categoryWithProducts->products_count,
            'last_page' => ceil($categoryWithProducts->products_count / $perPage),
        ];

        return CategoryResource::make($categoryWithProducts)->additional(['meta' => $meta]);
    }
}
