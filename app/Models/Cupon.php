<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    protected $table = 'cupons';
    protected $fillable = [
        'name', 'description', 'code', 'uses_number', 'minimal_cost', 'discount_amount' ,'state'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state)
    {
        if(!empty($state)){
            $query->where('state', $state);
        }
    }
}
