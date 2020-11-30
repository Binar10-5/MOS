<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MProduct extends Model
{
    protected $table = 'm_products';
    protected $fillable = [
        'id', 'category1_id', 'category2_id', 'category3_id', 'brand_id', 'state_id', 'name'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('m_products.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeMState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('m_products.state_id', $state_id);
        }
    }

    public function scopeCategory1($query, $category1)
    {
        if(!empty($category1)){
            $query->where('c1.category1_id', $category1);
        }
    }

    public function scopeCategory2($query, $category2)
    {
        if(!empty($category2)){
            $query->where('c2.category2_id', $category2);
        }
    }

    public function scopeCategory3($query, $category3)
    {
        if(!empty($category3)){
            $query->where('c3.category3_id', $category3);
        }
    }

    public function scopeBrand($query, $brand)
    {
        if(!empty($brand)){
            $query->where('m_products.brand_id', $brand);
        }
    }

    public function scopeLanguageC1($query, $language)
    {
        if(!empty($language)){
            $query->where('c1.language_id', $language);
        }
    }

    public function scopeLanguageC2($query, $language)
    {
        if(!empty($language)){
            $query->where('c2.language_id', $language);
        }
    }

    public function scopeLanguageC3($query, $language)
    {
        if(!empty($language)){
            $query->where('c3.language_id', $language);
        }
    }
}
