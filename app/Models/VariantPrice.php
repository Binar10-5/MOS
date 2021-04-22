<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantPrice extends Model
{
    protected $table = 'variant_price';
    protected $fillable = [
        'id', 'price', 'discount', 'final_price', 'country_id', 'variant_id'
    ];

}
