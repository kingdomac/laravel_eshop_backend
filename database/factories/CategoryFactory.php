<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->words(2, true);
        $slug = Str::slug($name) . Str::random(2);;
        return [
            'name' => $name,
            'slug' => $slug,
            'is_home' => false,
            'is_active' => true
        ];
    }

    public function inactive(): Factory
    {
        return $this->state([
            'is_active' => false
        ]);
    }

    public function isHome(): Factory
    {
        return $this->state([
            'is_home' => true
        ]);
    }
}
