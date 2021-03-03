<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $fillable = [
        'name', 'description', 'state', 'mondey_id', 'language_id'
    ];

}
