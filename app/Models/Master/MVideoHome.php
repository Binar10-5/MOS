<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MVideoHome extends Model
{
    protected $table = 'm_video_home';
    protected $fillable = [
        'id', 'name', 'state'
    ];
}
