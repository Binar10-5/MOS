<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProducts extends Model
{
    protected $table = 'orders_products';
    protected $fillable = [
        'order_id', 'product_id', 'name', 'color_code', 'color', 'price', 'discount', 'final_price', 'quantity'
    ];
}
