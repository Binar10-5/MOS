<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category1 extends Model
{
    protected $table = 'categories_1';
    protected $fillable = [
        'id', 'name', 'description', 'list_order', 'principal_id', 'language_id', 'state_id'
    ];
}
