<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category3 extends Model
{
    protected $table = 'categories_3';
    protected $fillable = [
        'id', 'name', 'description', 'list_order', 'principal_id', 'language_id', 'state_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('categories_3.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('categories_3.state_id', $state_id);
        }
    }

    public function scopeMState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('mc3.state_id', $state_id);
        }
    }

    public function scopeCategory2($query, $category2)
    {
        if(!empty($category2)){
            $query->where('mc3.category2_id', $category2);
        }
    }

    public function scopeLanguage($query, $language)
    {
        if(!empty($language)){
            $query->where('categories_3.language_id', $language);
        }else{
            $query->distinct('mc3.id');
        }
    }
}
