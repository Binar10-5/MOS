<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MCategory1 extends Model
{
    protected $table = 'm_categories_1';
    protected $fillable = [
        'id', 'name', 'state_id'
    ];
}
