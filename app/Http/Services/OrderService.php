<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Mail\OrderPurchased;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\OrderResource;
use App\Mail\OrderFailed;
use App\Notifications\AdminOrderPurchased;
use App\Notifications\AdminPaymentFailed;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class OrderService
{

    private $total = 0;
    private $orderProducts = [];

    public function handleOrder(array $order)
    {
        $items =  $order['items'];
        $itemsId = collect($items)->pluck('id');

        // load products from order
        $products = Product::query()->select('id', 'price', 'sale_price', 'in_stock')->whereIn('id', $itemsId)->get();

        // validate items in cart
        $areValidItems = $this->validateItemsInCart($products, $items);

        throw_if(
            !$areValidItems,
            ValidationException::withMessages([
                'quantity' => 'Some of purchased quantities are bigger than the one in the stock'
            ])
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

        // payement process
        $payment = $order['paymentMethod'] === PayPalService::PAYMENT_METHOD ? new PayPalService() : new CreditService();
        $isPaid = $payment->pay($this->total, $order);

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

    private function validateItemsInCart($products, $cartItems)
    {
        $isValid = true;
        foreach ($cartItems as $key => $item) {
            $product = $products->firstWhere('id', $item['id']);

            // Validate if the purchased quantity is smaller or equal to the stock quantity
            if ($item['quantity'] > $product->in_stock) {
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
    // public function handleOrder($orderRequest, $products)
    // {
    //     $items = $orderRequest['items'];
    //     $order  = new Order();
    //     DB::transaction(function () use ($order, $orderRequest, $items, $products) {
    //         // Create the order
    //         $order->fill([
    //             'user_id' => auth()->id(),
    //             'number' => random_int(1000, 9999) . Str::random(16) . time(),
    //             'buyer_name' => $orderRequest['name'],
    //             'buyer_email' => $orderRequest['email'],
    //             'buyer_phone' => $orderRequest['phone'],
    //             'buyer_address' => $orderRequest['address'],
    //             'status' => Order::TO_BE_SHIPPED,
    //             'total' => $this->total,
    //             'payment_method' => $orderRequest['paymentMethod']
    //         ])->save();

    //         // Attach the products to the order
    //         $toAttachItems = [];
    //         foreach ($items as $key => $value) {
    //             $product = $products->find($value['id']);
    //             if ($value['quantity'] <= $product->in_stock) {
    //                 $product->in_stock -= $value['quantity'];
    //                 $product->save();
    //             } else {
    //                 throw ValidationException::withMessages([
    //                     'error' => 'Some of quantities purchased are bigger than our stock'
    //                 ]);
    //             }

    //             $toAttachItems[$key]['product_id'] = $value['id'];
    //             $toAttachItems[$key]['price'] = $product->sale_price ?? $product->price;
    //             $toAttachItems[$key]['quantity'] = $value['quantity'];
    //         }

    //         $order->products()->attach($toAttachItems);
    //     });

    //     // Notify buyer and Admins with the order [commented to avoid throwing errors]
    //     /*-------------------------------------------------------------------------------
    //     Notification::send(User::admins()->get(), new NewAdminOrderSuccess($order));
    //     Notification::send($orderRequest['email'], new NewBuyerOrderSuccess($order));
    //     Mail::to($orderRequest['email'])->send(new OrderPurchased($order));
    //     -------------------------------------------------------------------------------*/

    //     //Mail::to($orderRequest['email'])->send(new OrderPurchased($order));
    //     //Notification::send(User::firstWhere('is_admin', true)->get(), new AdminOrderPurchased($order));
    //     //Mail::to($orderRequest['email'])->send(new OrderPurchased($order));
    //     return $order->load('products');
    // }

    // private function claculateTotal($products, $items): OrderService
    // {

    //     foreach ($products as $product) {
    //         $subTotal = $this->claculateSubTotal($product, $items);
    //         $this->total += $subTotal;
    //     }

    //     return $this;
    // }


    // private function claculateSubTotal(Product $product, $items): float
    // {
    //     $quantities = collect(
    //         array_filter($items, function ($item) use ($product) {
    //             return $item['id'] === $product->id;
    //         })
    //     )->pluck('quantity');
    //     $quantity = $quantities->sum();
    //     $subTotal = $quantity * $product->price;
    //     return $subTotal;
    // }


    // public static function handleOrder(PaymentMethod $paymentMethod, $orderRequest)
    // {
    //     $items = $orderRequest['items'];

    //     $itemsId = collect($items)->pluck('id');
    //     $products = Product::query()->select('id', 'price', 'sale_price')->whereIn('id', $itemsId)->get();

    //     $total = $this->claculateTotal($products, $items);

    //     //proceed with online payment
    //     $isPaymentSuccess = $paymentMethod->pay();

    //     // if true
    //     if ($isPaymentSuccess) {
    //         try {
    //             $order  = new Order();
    //             DB::transaction(function () use ($order, $orderRequest, $total, $items, $paymentMethod) {
    //                 // Create the order
    //                 $order->fill([
    //                     'user_id' => auth()->id(),
    //                     'number' => random_int(1000, 9999) . Str::random(16) . time(),
    //                     'buyer_name' => $orderRequest['name'],
    //                     'buyer_email' => $orderRequest['email'],
    //                     'buyer_address' => $orderRequest['address'],
    //                     'status' => Order::TO_BE_SHIPPED,
    //                     'total' => $total,
    //                     'payment_method' => get_class($paymentMethod)::PAYMENT_METHOD
    //                 ])->save();

    //                 // Attach the products to the order
    //                 $toAttachItems = [];
    //                 foreach ($items as $key => $value) {

    //                     $toAttachItems[$key]['product_id'] = $value['id'];
    //                     $toAttachItems[$key]['quantity'] = $value['quantity'];
    //                 }

    //                 $order->products()->attach($toAttachItems);
    //             });

    //             // Notify buyer and Admins with the order [commented to avoid throwing errors]
    //             /*-------------------------------------------------------------------------------
    //             Notification::send(User::admins()->get(), new NewAdminOrderSuccess($order));
    //             Notification::send($orderRequest['email'], new NewBuyerOrderSuccess($order));
    //             -------------------------------------------------------------------------------*/

    //             return $order->load('products');
    //         } catch (\Throwable $th) {
    //             throw ValidationException::withMessages([
    //                 'error' => 'server error, please contact administrator'
    //             ]);
    //         }
    //     } else {
    //         throw ValidationException::withMessages([
    //             'payment_error' => 'payement operation failed!'
    //         ]);
    //     }
    // }


    // private function claculateTotal($products, $items): float
    // {
    //     $total = 0;
    //     foreach ($products as $product) {
    //         $subTotal = $this->claculateSubTotal($product, $items);
    //         $total += $subTotal;
    //     }

    //     return $total;
    // }

}
