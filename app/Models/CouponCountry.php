<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCountry extends Model
{
    protected $table = 'coupons_country';
    protected $fillable = [
        'uses_number', 'maximum_uses', 'minimal_cost', 'discount_amount', 'state', 'coupon_id' ,'country_id'
    ];
}
