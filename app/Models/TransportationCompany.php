<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportationCompany extends Model
{
    protected $table = 'transportation_companies';
    protected $fillable = [
        'id', 'name', 'description', 'state'
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
