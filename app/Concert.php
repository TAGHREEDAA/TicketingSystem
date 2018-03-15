<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
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
        return $this->belongsToMany(Order::class, 'tickets');
//        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($email, $ticket_quantity)
    {
        $tickets = $this->findTickets($ticket_quantity);
        
        return $this->createOrder($email, $tickets);
    }


    public function reserveTickets($quantity)
    {
        // 1- find tickets
        $tickets = $this->findTickets($quantity);

        foreach ($tickets as $ticket)
        {
            $ticket->reserve();
        }

        // 2- reserve
        return $tickets;

    }


    public function findTickets($quantity)
    {
        // $tickets = $this->tickets()->whereNull('order_id')->take($ticket_quantity)->get();


        // refactoring
        $tickets = $this->tickets()->available()->take($quantity)->get();

        // if(($this->ticketsRemaining()) < $ticket_quantity)
        if(($tickets->count()) < $quantity)
        {
            throw new NotEnoughTicketsException();
        }

        return $tickets;

    }

    public function createOrder($email, $tickets)
    {

        return Order::forTickets($tickets, $email, $tickets->sum('price'));

        /*
        // 1- creates an order with the given email
        $order = Order::create([
            'email'=> $email,
            'charged_amount'=> $tickets->sum('price')]);
//            'charged_amount'=> $tickets->count() * $this->price]);

// assume that the all tickets have the same price
//        'charged_amount'=> $tickets->count() * $tickets->first()->concert->price]);



        // 2- creates tickets for the order with the given $ticket_quantity
        //foreach (range(1, $ticket_quantity) as $i)
        //{
            //$order->tickets()->create([]);
        //}

        // 2- Allocate tickets for the order with the given $ticket_quantity

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }
        return $order;

        */
    }

    public function addTickets($ticketsNo)
    {
        foreach (range(1, $ticketsNo) as $i)
        {
            $this->tickets()->create([]);
        }

    }

    public function ticketsRemaining()
    {
//        return $this->tickets()->whereNull('order_id')->count();
        return $this->tickets()->available()->count();
    }
}
