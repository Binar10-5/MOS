<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoHome extends Model
{
    protected $table = 'video_home';
    protected $fillable = [
        'id', 'name', 'description', 'video', 'principal_id', 'language_id'
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('video_home.name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state)
    {
        if(!empty($state)){
            $query->where('mv.state', $state);
        }
    }
}
