<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'm_banners';
    protected $fillable = [
        'id', 'name', 'description'
    ];
}
