<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;

class CategoryControllerTest extends TestCase
{
    use  WithFaker;

    /**
     * @test
     */
    function itListsAllAcitveCategories()
    {
        Category::factory(4)->create();
        Category::factory(4)->inactive()->create();

        $response = $this->get(route('categories.list'));

        $response->assertOk();
        $response->assertJsonCount(4, 'data');
    }

    /**
     * @test
     */
    function itListsAllActiveAndIsHomeCategoriesWithTheirProducts()
    {
        $homeCategory = Category::factory()->isHome()->create();
        $homeCategory2 = Category::factory()->isHome()->create();
        Category::factory()->inactive()->create();
        Category::factory()->isHome()->inactive()->create();
        $categoryIsActiveNotHome = Category::factory()->create();

        Product::factory(2)->for($homeCategory)->create();
        Product::factory(3)->for($homeCategory2)->create();

        Product::factory(4)->for($categoryIsActiveNotHome)->create();

        $response = $this->call('GET', route('categories.home'));
        //$response->dump();
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.id', $homeCategory2->id);
        $response->assertJsonPath('data.0.products_count', 3);
        $response->assertJsonPath('data.1.id', $homeCategory->id);
        $response->assertJsonPath('data.1.products_count', 2);
    }

    /**
     * @test
     */
    function itShowsCategoryDetailsWithProductsCountAndCustomPagination()
    {
        $category = Category::factory()->hasProducts(4)->create();

        Product::factory(4)->for($category)->create();

        $response = $this->get(route('category.products', ['category' => $category->slug]));
        //dd($response);
        $response->assertOk();
        $response->assertJsonFragment(['products_count' => 8, 'name' => $category->name]);
        $response->assertJsonPath('meta.last_page', 1);
    }
}
