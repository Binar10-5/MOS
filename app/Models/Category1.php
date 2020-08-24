<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category1 extends Model
{
    protected $table = 'categories_1';
    protected $fillable = [
        'id', 'name', 'description', 'list_order', 'principal_id', 'language_id', 'state_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('mc1.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('categories_1.state_id', $state_id);
        }
    }

    public function scopeMState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('mc1.state_id', $state_id);
        }
    }

    public function scopeLanguage($query, $language)
    {
        if(!empty($language)){
            $query->where('categories_1.language_id', $language);
        }else{
            $query->distinct('mc1.id');
        }
    }
}
