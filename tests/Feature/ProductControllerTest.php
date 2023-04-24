<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;

class ProductControllerTest extends TestCase
{
    use  WithFaker;
    /**
     * @test
     */
    function itShowsProductDetailsWithCategory()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $response = $this->get(route('product.show', ['product' => $product->slug]));
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $product->id,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ]
        ]);
        $response->assertJsonPath('data.category.id', $category->id);
    }

    /**
     * @test
     */

    function itReturnsRelatedProductsExceptTheCurrentOneAndTheLimit()
    {
        $cat_1 = Category::factory()->create();
        $cat_2 = Category::factory()->create();

        $currentProduct = Product::factory()->for($cat_1)->create();
        Product::factory(2)->for($cat_1)->create();
        Product::factory(10)->for($cat_2)->create();

        $response = $this->get(route('products.related', ['product' => $currentProduct->id]));
        $response_2 = $this->get(route('products.related', ['product' => $currentProduct->id, 'limit' => 1]));

        $response->assertOk()
            ->assertJsonCount(2, 'data.*')
            ->assertJsonPath('data.0.category_id',  $cat_1->id)
            ->assertJsonPath('data.1.category_id',  $cat_1->id);

        $response_2->assertOk()
            ->assertJsonCount(1, 'data.*')
            ->assertJsonPath('data.0.category_id', $cat_1->id);
    }

    /**
     * @test
     */
    function itListProductsWithSearchResult()
    {
        $product = Product::factory()->create([
            'name' => 'testing name',
            'description' => 'testing description'
        ]);

        $product2 = Product::factory()->create([
            'name' => 'another testing name',
            'description' => 'another testing description'
        ]);

        Product::factory(20)->create();

        $query = 'testing';
        $response = $this->get(route('products.list', ['query' => $query]));

        $response2 = $this->get(route('products.list', ['query' => $query, 'per_page' => 1]));
        //$response->dd();
        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.1.id', $product2->id);

        $response2->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /**
     * @test
     */
    function itListsAllproductsWithPaginationWithoutFiltring()
    {
        Product::factory(20)->create();

        $response = $this->get(route('products.list'));
        $response2 = $this->get(route('products.list', ['per_page' => 5]));

        $response->assertOk()
            ->assertJsonCount(10, 'data');
        $response2->assertOk()->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 5);
    }
}