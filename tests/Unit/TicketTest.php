<?php

namespace Tests\Unit;

use App\Concert;
use App\Ticket;
use Carbon\Carbon;
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

    /** @test */
    public function CanReleaseTicketNewImplementation()
    {
//        $ticket = factory(Ticket::class)->create();
//        $ticket->reserve();

//        $ticket = factory(Ticket::class)->create(['reserved_at' => Carbon::now()]);
        $ticket = factory(Ticket::class)->states('reserved')->create();

        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();
        $this->assertNull($ticket->fresh()->reserved_at);

    }


    /** @test */
    public function TicketCanBeReserved()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        // to make sure saving this ticket ---- > $ticket->fresh()
        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

}

