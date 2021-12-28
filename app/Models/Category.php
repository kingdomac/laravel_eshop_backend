<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Staudenmeir\EloquentEagerLimit\HasEagerLimit;


class Category extends Model
{
    use HasFactory, HasEagerLimit;

    //protected $guarded = [];
    protected $attributes = [
        'is_home' => false,
        'is_active' => true
    ];

    protected $casts = [
        'is_home' => 'boolean',
        'is_active' => 'boolean',
        'products_count' => 'integer'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeIsHome(Builder $builder)
    {
        $builder->whereIsHome(true);
    }

    protected static function booted()
    {
        static::addGlobalScope('isActive', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }
}
