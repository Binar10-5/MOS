<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MProduct extends Model
{
    protected $table = 'products_variant';
    protected $fillable = [
        'id', 'category1_id', 'category2_id', 'category3_id', 'brand_id'
    ];
}
