<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';
    protected $fillable = [
        'name', 'description', 'state', 'logo', 'public_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('brands.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopestate($query, $state)
    {
        if(!empty($state)){
            $query->where('brands.state', $state);
        }
    }
}
