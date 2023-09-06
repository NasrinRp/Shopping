<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\HasMany;

class Product extends Model
{
    use HasFactory,
        SoftDeletes;

    protected $collection = 'product';

    protected $fillable = [
        'name',
        'price',
        'inventory',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(
            OrderItem::class,
            'product_id'
        );
    }

}
