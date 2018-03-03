<?php

namespace App;

use App\Http\Controllers\ConcertOrdersController;
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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function orderTickets($email, $ticket_quantity)
    {
        // 1- creates an order with the given email

        $order = $this->orders()->create(['email'=> $email]);


        // 2- creates tickets for the order with the given $ticket_quantity
        foreach (range(1, $ticket_quantity) as $i)
        {
            $order->tickets()->create([]);
        }

        return $order;
    }
}
