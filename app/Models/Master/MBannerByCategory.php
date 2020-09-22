<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MBannerByCategory extends Model
{
    protected $table = 'm_banners_by_category';
    protected $fillable = [
        'id', 'name', 'category_id', 'state_id'
    ];
}
