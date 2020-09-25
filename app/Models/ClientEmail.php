<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientEmail extends Model
{
    protected $table = 'client_email';
    protected $fillable = [
        'id', 'email', 'state'
    ];

    public function scopeEmail($query, $email)
    {
        if(!empty($email)){
            $query->where('email', 'LIKE', '%'.$email.'%');
        }
    }

    public function scopeRange($query, $date_start, $date_end)
    {
        if(!empty($date_start) && !empty($date_end)){
            $query->whereBetween('created_at', [$date_start.' 00:00:00', $date_end.' 23:59:59']);
        }
    }
}
