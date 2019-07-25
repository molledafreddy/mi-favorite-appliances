<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dishwasher extends Model
{
    public function scopeSearch($query, $target)
    {
        if ($target != '') {
            return $query->where('title', $target)->orWhere('price', $target);
        }
    }
}
