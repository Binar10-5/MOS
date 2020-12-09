<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offers';
    protected $fillable = [
        'id', 'name', 'description', 'minimal_cost', 'discount_amount', 'state', 'type', 'maximum_cost'
    ];
}
