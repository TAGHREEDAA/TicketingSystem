<?php

namespace Tests\Unit;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function CanReleaseTicket()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);

        $order = $concert->orderTickets('test@gmail.com',1);

        $ticket = $order->tickets()->first();

        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();
        $this->assertNull($ticket->fresh()->order_id);

    }
}

