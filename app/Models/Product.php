<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Staudenmeir\EloquentEagerLimit\HasEagerLimit;


class Product extends Model
{
    use HasFactory, HasEagerLimit;

    protected $attributes = [
        'in_stock' => 1,
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'in_stock' => 'integer',
        'category_id' => 'integer',
        'quantity' => 'integer',

    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')->withPivot(['price', 'quantity']);
    }
}
