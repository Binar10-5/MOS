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
