<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banner';
    protected $fillable = [
        'id', 'name', 'description', 'img_short', 'img_median', 'img_big', 'link', 'state_id', 'public_id', 'order_by', 'principal_id', 'language_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('banner.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state)
    {
        if(!empty($state)){
            $query->where('banner.state_id', $state);
        }
    }


    protected $hidden = [
        'public_id'
    ];
}
