<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';
    protected $fillable = [
        'id', 'name', 'color_code', 'color', 'principal_id', 'quantity', 'price', 'category1_order', 'category2_order', 'category3_order', 'state_id',
        'new_product', 'favorite', 'cruelty_free'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('product_variants.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeColor($query, $color)
    {
        if(!empty($color)){
            $query->where('product_variants.color', 'LIKE', '%'.$color.'%');
        }
    }

    public function scopeState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('product_variants.state_id', $state_id);
        }
    }

    public function scopecategory3($query, $category3)
    {
        if(!empty($category3)){
            $query->orderBy('product_variants.category3_order', $category3);
        }
    }
}
