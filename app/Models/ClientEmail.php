<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientEmail extends Model
{
    protected $table = 'client_email';
    protected $fillable = [
        'id', 'email', 'state'
    ];
}
