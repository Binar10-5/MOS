<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MCategory2 extends Model
{
    protected $table = 'm_categories_2';
    protected $fillable = [
        'id', 'name', 'state_id'
    ];
}
