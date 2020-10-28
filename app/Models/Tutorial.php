<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $table = 'tutorials';
    protected $fillable = [
        'id', 'title', 'description', 'image', 'public_id', 'content', 'slider', 'principal_id', 'language_id', 'state'
    ];

    public function scopeLanguage($query, $language)
    {
        if(!empty($language)){
            $query->where('tutorials.language_id', $language);
        }else{
            $query->distinct('mt.id');
        }
    }

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('mt.title', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('mt.state', $state_id);
        }
    }
}
