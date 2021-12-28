<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    const UNPAID = 1;
    const PAID = 2;
    const TO_BE_SHIPPED = 3;
    const SHIPPED = 4;
    const DELIVERED = 5;

    protected $attributes = [
        'payment_method' => 1
    ];

    protected $casts = [
        'status' => 'integer',
        'total' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')->withPivot(['price', 'quantity']);
    }
}
