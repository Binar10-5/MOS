<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MTutorial extends Model
{
    protected $table = 'm_tutorials';
    protected $fillable = [
        'id', 'title', 'description', 'state'
    ];
}
