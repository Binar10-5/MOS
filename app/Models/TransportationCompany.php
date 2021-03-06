<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportationCompany extends Model
{
    protected $table = 'transportation_companies';
    protected $fillable = [
        'id', 'name', 'description', 'state', 'country_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('transportation_companies.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state)
    {
        if(!empty($state)){
            $query->where('transportation_companies.state', $state);
        }
    }
}
