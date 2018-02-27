<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['datetime'];

    /**
     * Defining A Mutator
     */

    public function getFormattedDateAttribute()
    {
        return $this->datetime->format('F j, Y');
    }

    public function getFormattedTimeAttribute()
    {
        return $this->datetime->format('g:i a');
    }

    public function getDollarsPriceAttribute()
    {
        // if I stored the price as integer num
        //return number_format($this->price/100, 2).' $';

        // if stored price as float num
        return $this->price.' $';
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
