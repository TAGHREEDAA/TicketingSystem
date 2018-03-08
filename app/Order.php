<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
//            $ticket->update(['order_id'=> null]);
            $ticket->release();
        }

        $this->delete();
    }

    public function ticketQuantity(){
        return $this->tickets()->count();
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'charged_amount' =>  $this->ticketQuantity() * $this->concert->price ,
        ];
    }
}
