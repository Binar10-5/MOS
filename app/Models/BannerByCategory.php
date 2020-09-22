<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerByCategory extends Model
{
    protected $table = 'banners_by_category';
    protected $fillable = [
        'id', 'name', 'description', 'img_short', 'img_median', 'img_big', 'link', 'public_id', 'order_by', 'principal_id', 'language_id', 'state_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('banners_by_category.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state)
    {
        if(!empty($state)){
            $query->where('mb.state', $state);
        }
    }


    protected $hidden = [
        'public_id'
    ];
}
