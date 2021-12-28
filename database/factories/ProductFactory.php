<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->words(2, true);
        $slug = Str::slug($name) . Str::random(2);
        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => $slug,
            'cover' => rand(1, 11) . ".jpg", //$this->faker->image(dir: 'public/storage/images', width: 250),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(1_00, 1_000),
            'in_stock' => $this->faker->numberBetween(1, 50)
        ];
    }
}
