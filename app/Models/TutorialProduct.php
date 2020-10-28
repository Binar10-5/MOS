<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorialProduct extends Model
{
    protected $table = 'tutorial_products';
    protected $fillable = [
        'product_id', 'tutorial_id', 'state'
    ];
}
