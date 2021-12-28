<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'number' => $this->faker->numerify('########'),
            'buyer_name' => $this->faker->name(),
            'buyer_email' => $this->faker->safeEmail(),
            'buyer_address' => $this->faker->address(),
            'status' => Order::TO_BE_SHIPPED,
            'total' => $this->faker->numberBetween(1_00, 2_000)
        ];
    }

    public function toBeShipped(): Factory
    {
        return $this->state([
            'status' => Order::TO_BE_SHIPPED
        ]);
    }

    public function shipped(): Factory
    {
        return $this->state([
            'status' => Order::SHIPPED
        ]);
    }
}
