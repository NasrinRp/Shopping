<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory,
        SoftDeletes;

    protected $connection = "mongodb";

    protected $collection = 'order_item';

    protected $fillable = [
        'product_id',
        'order_id',
        'unit_price',
        'count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            Order::class,
            'order_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }
}
