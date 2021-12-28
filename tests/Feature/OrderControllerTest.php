<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Arr;
use App\Mail\OrderPurchased;
use App\Http\Services\CreditService;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use App\Notifications\AdminOrderPurchased;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class OrderControllerTest extends TestCase
{
    use  WithFaker;

    private function calculateTotal($products, $quantities)
    {
        $total = 0;
        foreach ($products as $key => $value) {
            $total += (float)(($value->price) * $quantities[$key]);
        }
        return $total = number_format($total, 2, '.', '');
    }

    /**
     * @test
     */
    public function itCheckoutSuccessWithCreditPayment()
    {

        Mail::fake();
        Queue::fake();
        Notification::fake();

        User::factory()->create(['is_admin' => true]);
        Product::factory(3)->create(['in_stock' => 10]);

        $productOne = Product::factory()->create(['in_stock' => 5]);
        $productTwo = Product::factory()->create(['in_stock' => 3]);

        $order = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'phone' => '34444',
            'paymentMethod' => CreditService::PAYMENT_METHOD,
            'cardNumber' => '1234567',
            'expiryDate' => '11/23',
            'securityNumber' => '123',
            'items' => [
                ['id' => $productOne->id, 'quantity' => 2],
                ['id' => $productTwo->id, 'quantity' => 1]
            ]
        ];


        $response = $this->postJson(
            route('orders.checkout'),
            $order
        );

        $total = $this->calculateTotal([$productOne, $productTwo], [2, 1]);

        User::factory()->create(['is_admin' => true]);
        Queue::push(AdminOrderPurchased::class);

        Mail::assertQueued(OrderPurchased::class);
        //Notification::assertSentTo($admin, AdminOrderPurchased::class);
        Queue::assertPushed(AdminOrderPurchased::class);

        //$response->dd();

        $response->assertCreated()
            ->assertJsonCount(2, 'data.products')
            ->assertJsonPath('data.products.0.id', $productOne->id)
            ->assertJsonPath('data.products.1.id', $productTwo->id)
            ->assertJsonPath('data.products.0.quantity', '2')
            ->assertJsonPath('data.products.1.quantity', '1')
            ->assertJsonPath('data.products.0.in_stock', (int)($productOne->in_stock - 2))
            ->assertJsonPath('data.products.1.in_stock', (int)($productTwo->in_stock - 1))
            ->assertJsonPath('data.payment_method', CreditService::PAYMENT_METHOD)
            ->assertJsonPath('data.status', Order::TO_BE_SHIPPED)
            ->assertJsonPath('data.total', $total);
        //dd($response->json());
        $this->assertDatabaseCount('order_product', 2);
        $this->assertDatabaseHas(
            'order_product',
            [
                "product_id" => $productOne->id,
                "product_id" => $productTwo->id,
                "order_id" => $response->json('data.id')
            ]
        );
    }

    /**
     * @test
     */
    public function itFailsWhenQuantityBiggerThenTheStock()
    {
        Product::factory(5)->create(['in_stock' => 2]);

        $productOne = Product::factory()->create(['in_stock' => 2]);
        $productTwo = Product::factory()->create(['in_stock' => 1]);

        $order = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'phone' => '34444',
            'paymentMethod' => CreditService::PAYMENT_METHOD,
            'cardNumber' => '1234567',
            'expiryDate' => '11/23',
            'securityNumber' => '123',
            'items' => [
                ['id' => $productOne->id, 'quantity' => 2],
                ['id' => $productTwo->id, 'quantity' => 3]
            ]
        ];

        $response = $this->postJson(
            route('orders.checkout'),
            $order
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);
    }


    /**
     * @test
     */
    public function itValidateTheOrderWithCreditCardMethod()
    {
        Product::factory(5)->create();

        $order = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'phone' => "3243243224",
            'items' => []
        ];

        $order1 = array_merge($order, [
            'paymentMethod' => 1,
            'cardNumber' => '',
            'expiryDate' => '',
            'securityNumber' => '',
        ]);
        $response1 = $this->postJson(
            route('orders.checkout'),
            $order1
        );
        $response1->assertUnprocessable()->assertInvalid(
            array_keys(Arr::except(
                $order1,
                ['name', 'email', 'address', 'phone', 'paymentMethod']
            ))
        );

        $order2 = array_merge($order, [
            'paymentMethod' => 1,
            'cardNumber' => 'asd',
            'expiryDate' => 'asd',
            'securityNumber' => 'asd',
        ]);
        $response2 = $this->postJson(
            route('orders.checkout'),
            $order2
        );
        $response2->assertUnprocessable()->assertInvalid(
            array_keys(Arr::except(
                $order2,
                ['name', 'email', 'address', 'phone', 'paymentMethod']
            ))
        );

        $order3 = array_merge($order, [
            'paymentMethod' => 1,
            'cardNumber' => '123',
            'expiryDate' => '1123',
            'securityNumber' => 'asd',
        ]);
        $response3 = $this->postJson(
            route('orders.checkout'),
            $order2
        );
        $response3->assertUnprocessable()->assertInvalid(
            array_keys(Arr::except(
                $order3,
                ['name', 'email', 'address', 'phone', 'paymentMethod', 'cardNumber']
            ))
        );

        $order4 = array_merge($order, [
            'paymentMethod' => 1,
            'cardNumber' => '123',
            'expiryDate' => '11/17',
            'securityNumber' => 'asd',
        ]);
        $response4 = $this->postJson(
            route('orders.checkout'),
            $order2
        );
        $response4->assertUnprocessable()->assertInvalid(
            array_keys(Arr::except(
                $order4,
                ['name', 'email', 'address', 'phone', 'paymentMethod', 'cardNumber']
            ))
        );

        $order5 = array_merge($order, [
            'paymentMethod' => 1,
            'cardNumber' => '123',
            'expiryDate' => '11/23',
            'securityNumber' => '1234',
        ]);
        $response5 = $this->postJson(
            route('orders.checkout'),
            $order2
        );
        $response5->assertUnprocessable()->assertInvalid(
            array_keys(Arr::except(
                $order5,
                ['name', 'email', 'address', 'phone', 'paymentMethod']
            ))
        );

        $order6 = array_merge($order, [
            'paymentMethod' => 1,
            'cardNumber' => '123',
            'expiryDate' => '11/23',
            'securityNumber' => '123',
        ]);
        $response6 = $this->postJson(
            route('orders.checkout'),
            $order2
        );
        $response6->assertUnprocessable()->assertInvalid(
            array_keys(Arr::except(
                $order6,
                ['name', 'email', 'address', 'phone', 'paymentMethod', 'securityNumber']
            ))
        );
    }
}
