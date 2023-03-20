<?php

namespace App\Http\Repositries;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepo
{

    public function getAllOrdersByAuthUser(): Collection
    {
        return Order::query()
            ->with('products')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
