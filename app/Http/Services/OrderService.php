<?php

namespace App\Http\Services;

use App\Exceptions\Order\OrderInvalidException;
use App\Http\Repositries\ProductRepo;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Mail\OrderPurchased;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\OrderResource;
use App\Http\Services\Payment\Enums\PaymentMethodEnum;
use App\Http\Services\Payment\PaymentBuilder;
use App\Mail\OrderFailed;
use App\Notifications\AdminOrderPurchased;
use App\Notifications\AdminPaymentFailed;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class OrderService
{

    private $total = 0;
    private $orderProducts = [];

    public function __construct()
    {
    }

    public function handleOrder(array $order)
    {
        $items =  $order['items'];
        $itemsId = collect($items)->pluck('id')->toArray();

        // load products from order
        $products = (new ProductRepo())->getAllProductsInsideOrder($itemsId);

        // validate items in cart
        $areValidQuantityItems = $this->validateItemsQuantityInCart($products, $items);

        throw_if(
            !$areValidQuantityItems,
            new OrderInvalidException(['quantity' => 'Some of purchased quantities are bigger than the one in the stock'])
            // ValidationException::withMessages([
            //     'quantity' => 'Some of purchased quantities are bigger than the one in the stock'
            // ])
        );

        $orderObj = new Order();

        // insert order as unpaid and attach products
        $orderObj = DB::transaction(
            function () use ($order, $orderObj) {
                // insert order as unpaid
                $orderObj->fill([
                    'user_id' => auth()->id(),
                    'number' => random_int(1000, 9999) . Str::random(16) . time(),
                    'buyer_name' => $order['name'],
                    'buyer_email' => $order['email'],
                    'buyer_phone' => $order['phone'],
                    'buyer_address' => $order['address'],
                    'status' => Order::UNPAID,
                    'total' => $this->total,
                    'payment_method' => $order['paymentMethod']
                ])->save();

                // Attach products to the order
                $orderObj->products()->attach($this->orderProducts);
                return $orderObj;
            }
        );

        $payment = PaymentBuilder::build(PaymentMethodEnum::from((int)$order['paymentMethod']));
        $isPaid = $payment->pay($this->total, $order);
        $isPaid = true;
        if (!$isPaid) {
            Notification::send(User::admins()->get(), new AdminPaymentFailed($orderObj));
            Mail::to($orderObj->buyer_email)->send(new OrderFailed());
            throw ValidationException::withMessages(['payment' => 'Payment process fail']);
        }

        // Update Order status to be shipped
        $orderObj->fill(["status" => Order::TO_BE_SHIPPED]);
        $orderObj->save();

        // Decrease quantity in product stock
        $this->decreaseQauntityInStock($products, $items);


        // Notify user and admins with the order [commented to avoid throwing errors in testing]
        //     /*-------------------------------------------------------------------------------
        Notification::send(User::admins()->get(), new AdminOrderPurchased($orderObj));
        Mail::to($orderObj->buyer_email)->send(new OrderPurchased($orderObj, config('frontapp.url') . "/order/$orderObj->number"));
        //     -------------------------------------------------------------------------------*/

        return OrderResource::make($orderObj->load('products'));
    }

    private function validateItemsQuantityInCart($products, $cartItems)
    {
        $isValid = true;
        foreach ($cartItems as $key => $item) {
            $product = $products->firstWhere('id', $item['id']);

            // Validate if the purchased quantity is smaller or equal to the stock quantity
            if ($item['quantity'] > $product->in_stock || $item['quantity'] <= 0) {
                $isValid = false;
                $this->reset();
                break;
            }

            // fill the array to attach to the order
            $this->orderProducts[$key]['product_id'] = $item['id'];
            $this->orderProducts[$key]['price'] = $product->sale_price ?? $product->price;
            $this->orderProducts[$key]['quantity'] = $item['quantity'];

            $subTotal = $this->claculateSubTotal($product, $item);
            $this->total += $subTotal;
        }

        return $isValid;
    }

    private function decreaseQauntityInStock($products, $cartItems)
    {
        foreach ($cartItems as $key => $item) {
            $product = $products->firstWhere('id', $item['id']);
            $product->in_stock -= $item['quantity'];
            $product->save();
        }
    }

    private function reset()
    {
        $this->orderProducts = [];
        $this->total = 0;
    }

    private function claculateSubTotal(Product $product, $item)
    {
        $price = $product->sale_price ?? $product->price;
        return $price * $item['quantity'];
    }
}
