<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appliance extends Model
{
    protected $fillable = [
        'id','title', 'price', 'price_previus', 'extended_warranty', 'interest', 'status_warranty', 'description', 'image', 'logo','interest','recycling','type', 'links'
    ];

    public function scopeSearch($query, $target)
    {
        if ($target != '') {
            return $query->where('title', $target)->orWhere('price', $target);
        }
    }
}