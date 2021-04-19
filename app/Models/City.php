<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'city';
    protected $fillable = [
        'id', 'dane_code', 'name', 'department_dane_code', 'department_name', 'region_name', 'delivery_fee', 'delivery_time', 'state', 'country_id'
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
