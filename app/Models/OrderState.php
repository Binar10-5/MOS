<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderState extends Model
{
    protected $table = 'order_state_id';
    protected $fillable = [
        'name', 'description'
    ];
}
