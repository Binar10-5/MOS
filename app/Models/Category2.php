<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category2 extends Model
{
    protected $table = 'categories_2';
    protected $fillable = [
        'id', 'name', 'description', 'list_order', 'principal_id', 'language_id', 'state_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('mc2.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('categories_2.state_id', $state_id);
        }
    }

    public function scopeMState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('mc2.state_id', $state_id);
        }
    }

    public function scopeCategory1($query, $category1)
    {
        if(!empty($category1)){
            $query->where('mc2.category1_id', $category1);
        }
    }

    public function scopeLanguage($query, $language)
    {
        if(!empty($language)){
            $query->where('categories_2.language_id', $language);
        }else{
            $query->distinct('mc2.id');
        }
    }
}
