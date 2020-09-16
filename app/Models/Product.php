<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'id', 'name', 'description', 'color', 'color_code', 'variant_id', 'language_id', 'tracking', 'image1', 'image2', 'image3', 'image4', 'image5', 'public_id', 'state_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('mp.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeLanguageName($query, $name)
    {
        if(!empty($name)){
            $query->where('products.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeVState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('pv.state_id', $state_id);
        }
    }

    public function scopeState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('products.state_id', $state_id);
        }
    }

    public function scopeMState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('mp.state_id', $state_id);
        }
    }

    public function scopeCategory1($query, $category1)
    {
        if(!empty($category1)){
            $query->where('mc1.id', $category1);
        }
    }

    public function scopeCategory2($query, $category2)
    {
        if(!empty($category2)){
            $query->where('mc2.id', $category2);
        }
    }

    public function scopeCategory3($query, $category3)
    {
        if(!empty($category3)){
            $query->where('mc3.id', $category3);
        }
    }

    public function scopeLanguage($query, $language)
    {
        if(!empty($language)){
            $query->where('products.language_id', $language);
        }else{
            $query->distinct('mp.id');
        }
    }
}
