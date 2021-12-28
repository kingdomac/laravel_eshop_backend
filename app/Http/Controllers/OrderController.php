<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Services\OrderService;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderController extends Controller
{

    public function index(): JsonResource
    {
        $orders = Order::with('products')->where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
        return OrderResource::collection($orders);
    }

    public function show(Order $order): JsonResource
    {
        return OrderResource::make($order->load('products'));
    }

    public function purchase(StoreOrderRequest $request): JsonResource
    {
        $purchasedOrder = $request->validated();
        return (new OrderService())->handleOrder($purchasedOrder);
    }
}
