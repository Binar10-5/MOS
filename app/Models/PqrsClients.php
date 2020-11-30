<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqrsClients extends Model
{
    protected $table = 'pqrs_client';
    protected $fillable = [
        'id', 'name', 'last_name', 'email', 'cell_phone'
    ];

    public function scopeRange($query, $date_start, $date_end)
    {
        if(!empty($date_start) && !empty($date_end)){
            $query->whereBetween('created_at', [$date_start.' 00:00:00', $date_end.' 23:59:59']);
        }
    }
}
