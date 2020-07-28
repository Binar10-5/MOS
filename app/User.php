<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'full_name', 'email', 'password', 'address', 'code_email_verify', 'code_password_verify', 'pasword_verify', 'email_verify', 'state_id', 'cell_phone', 'dni'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function scopeName($query, $name)
    {
        if(!empty($name)){
            $query->where('full_name', 'LIKE', '%'.$name.'%');
        }
    }

    public function scopeState($query, $state)
    {
        if(!empty($state)){
            $query->where('state_id', $state);
        }
    }

    public function scopeDni($query, $dni)
    {
        if(!empty($dni)){
            $query->where('dni', 'LIKE', '%'.$dni.'%');
        }
    }

    public function scopeRange($query, $date_start, $date_end)
    {
        if(!empty($date_start) && !empty($date_end)){
            $query->whereBetween('created_at', [$date_start.' 00:00:00', $date_end.' 23:59:59']);
        }
    }

    public function scopeByName($query, $name)
    {
        if(!empty($name)){
            $query->orderBy('last_name', $name);
        }
    }

    public function scopeByDni($query, $dni)
    {
        if(!empty($dni)){
            $query->orderBy('dni', $dni);
        }
    }

    public function scopeByCreatedAt($query, $created)
    {
        if(!empty($created)){
            $query->orderBy('created_at', $created);
        }
    }
}
