<?php

namespace Tests\Unit;

use App\Concert;
use App\Order;
use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{

    use DatabaseMigrations;


    /** @test */
    public function createOrderFromTicketsEmailAndAmount()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(5);

        $this->assertEquals(5, $concert->ticketsRemaining());

        $order = Order::forTickets($concert->findTickets(3), 'test@gmail.com', 300);
        
        $this->assertEquals('test@gmail.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(300, $order->charged_amount);
        $this->assertEquals(2, $concert->ticketsRemaining());

    }


    /** @test */
    public function convertOrderToArray()
    {
        $this->disableExceptionHandling();
        // Arrange: 1- create a concert and add tickets
        $concert = factory(Concert::class)->create(['price'=> 100]);
        $concert->addTickets(5);

        // Arrange: 2- order tickets
        $order = $concert->orderTickets('test@gmail.com', 5);

        $results = $order->toArray();
        $this->assertEquals([
            'email' => 'test@gmail.com',
            'ticket_quantity' => 5,
            'charged_amount' => 500,
        ], $results);

    }

//    cancelled test
//    public function releaseTicketsWhenCancelOrder()
//    {
//        $this->disableExceptionHandling();
//        // Arrange: 1- create a concert and add tickets
//        $concert = factory(Concert::class)->create();
//        $concert->addTickets(10);
//
//        // Arrange: 2- order tickets
//        $order = $concert->orderTickets('test@gmail.com', 5);
//        $this->assertEquals(5, $concert->ticketsRemaining());
//
//        // Act: cancel order
//        $order->cancel();
//
//        // assert tickets remaining
//        $this->assertEquals(10, $concert->ticketsRemaining());
//
//        $this->assertNull(Order::find($order->id));
//
//    }
}
