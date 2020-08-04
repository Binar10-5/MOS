<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banner';
    protected $fillable = [
        'id', 'name', 'description', 'img_short', 'img_median', 'img_big', 'link', 'public_id', 'order_by', 'principal_id', 'language_id', 'state_id'
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
            $query->where('mb.state_id', $state);
        }
    }


    protected $hidden = [
        'public_id'
    ];
}
