<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MBanner extends Model
{
    protected $table = 'm_banners';
    protected $fillable = [
        'id', 'name'
    ];
}
