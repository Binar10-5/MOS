<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MCategory3 extends Model
{
    protected $table = 'm_categories_3';
    protected $fillable = [
        'id', 'name', 'state_id', 'category2_id'
    ];
}
