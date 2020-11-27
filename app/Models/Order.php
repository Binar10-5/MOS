<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'order_number', 'client_name', 'client_last_name', 'client_dni', 'client_address', 'client_cell_phone', 'client_email', 'subtotal', 'total', 'state_id'
        , 'coupon_id', 'payment_data', 'language_id', 'transportation_company_id' ,'tracking_number', 'city_id', 'delivery_fee', 'facturation_date', 'tracking'
    ];

    public function scopeName($query, $client_name)
    {
        if(!empty($client_name)){
            $query->where('orders.client_name', 'LIKE', '%'.$client_name.'%');
        }
    }

    public function scopeDNI($query, $client_dni)
    {
        if(!empty($client_dni)){
            $query->where('orders.client_dni', 'LIKE', '%'.$client_dni.'%');
        }
    }

    public function scopeCity($query, $city_id)
    {
        if(!empty($city_id)){
            $query->where('orders.city_id', $city_id);
        }
    }

    public function scopeFacturation($query, $start, $end)
    {
        if(!empty($start) && !empty($end)){
            $query->whereBetween('orders.facturation_date', [$start, $end]);
        }
    }

    public function scopeCode($query, $order_number)
    {
        if(!empty($order_number)){
            $query->where('orders.order_number', 'LIKE', '%'.$order_number.'%');
        }
    }

    public function scopeState($query, $state_id)
    {
        if(!empty($state_id)){
            $query->where('orders.state_id', $state_id);
        }
    }

    public function scopeTotal($query, $min, $max)
    {
        if(!empty($min) && !empty($max)){
            $query->whereBetween('orders.total', [(int)$min, (int)$max]);
        }
    }

    public function scopeSubtotal($query, $min, $max)
    {
        if(!empty($min) && !empty($max)){
            $query->whereBetween('orders.subtotal', [$min, $max]);
        }
    }

    public function scopeCreated($query, $date_star, $date_end)
    {
        if(!empty($date_star) && !empty($date_end)){
            $query->whereBetween('orders.created_at', [$date_star, $date_end]);
        }
    }
}
