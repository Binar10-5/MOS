<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'id', 'name', 'description', 'variant_id', 'language_id', 'tracking', 'image1', 'image2', 'image3', 'image4', 'image5', 'public_id'
    ];
}
