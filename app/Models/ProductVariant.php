<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'products_variant';
    protected $fillable = [
        'id', 'name', 'color_code', 'color', 'principal_id', 'quantity', 'price', 'category1_order', 'category2_order', 'category3_order'
    ];
}
