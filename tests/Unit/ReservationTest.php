<?php

namespace Tests\Unit;

use App\Concert;
use App\Reservation;
use App\Ticket;
use \Mockery;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery\Mock;
use PhpParser\Node\Expr\Cast\Object_;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function totalCostCalculation()
    {
//        $concert = factory(Concert::class)->create(['price'=> 100]);
//        $concert->addTickets(5);
//
//        $tickets = $concert->findTickets(5);



        // will not work because the getPriceAttribute depends on the concert which the tickets belong to
//        $tickets = collect([
//
//           new Ticket(['price' => 100]),
//           new Ticket(['price' => 100]),
//           new Ticket(['price' => 100]),
//        ]);


        // mockery doesn't support mocking things that are accessible via magic methods
        // $ticket->price --- magic method getPriceAttribute depending on the concert price

//        $tickets = collect([
//            Mockery::mock(Ticket::class, ['price'=> 100 ]),
//            Mockery::mock(Ticket::class, ['price'=> 100 ]),
//            Mockery::mock(Ticket::class, ['price'=> 100 ]),
//
//        ]);

        // the simplest way
        $tickets = collect([
            (object)['price' => 100] ,
            (object)['price' => 100] ,
            (object)['price' => 100] ,
            (object)['price' => 100] ,
            (object)['price' => 100] ,
        ]);

        $reservation = new Reservation($tickets, 'test@gmail.com');

        $this->assertEquals(500, $reservation->totalCost());


    }

    /** @test */
    public function retrievingReservationTickets()
    {
        // the simplest way
        $tickets = collect([
            (object)['price' => 100] ,
            (object)['price' => 100] ,
            (object)['price' => 100] ,
            (object)['price' => 100] ,
            (object)['price' => 100] ,
        ]);

        $reservation = new Reservation($tickets, 'test@gmail.com');

        $this->assertEquals($tickets, $reservation->tickets());

    }

    /** @test */
    public function retrievingReservationEmail()
    {

        $reservation = new Reservation(collect(), 'test@gmail.com');

        $this->assertEquals('test@gmail.com', $reservation->email());

    }

    /** @test */
    public function ReleaseReservedTicketsWhenCancelReservation()
    {

//        $ticket1 = Mockery::mock(Ticket::class);
//        $ticket1->shouldReceive('release')->once();

//        $ticket2 = Mockery::mock(Ticket::class);
//        $ticket2->shouldReceive('release')->once();

//        $ticket3 = Mockery::mock(Ticket::class);
//        $ticket3->shouldReceive('release')->once();


        // shouldReceive
        // if the ticket->release wasn't called once the test will fail
//        $tickets = collect([$ticket1, $ticket2, $ticket3]);

//        $tickets =collect([
//            Mockery::mock(Ticket::class, function ($mock) {
//                $mock->shouldReceive('release')->once();
//            }),
//            Mockery::mock(Ticket::class, function ($mock) {
//                $mock->shouldReceive('release')->once();
//            }),
//            Mockery::mock(Ticket::class, function ($mock) {
//                $mock->shouldReceive('release')->once();
//            })
//
//        ]);


//        $tickets = collect([
//            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
//            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
//            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock()
//        ]);


// to separate arrange step from assert
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class)
        ]);

        $reservation = new Reservation($tickets, 'test@gmail.com');

        $reservation->cancel();

        foreach ($tickets as $ticket)
        {
            $ticket->shouldHaveReceived('release');
        }

    }


    /** @test */
    public function CompleteReservation()
    {
        $concert = factory(Concert::class)->create(['price'=>100]);

        $tickets = factory(Ticket::class, 3)->create(['concert_id'=> $concert->id]);

        $reservation = new Reservation($tickets, 'test@gmail.com');

        $order = $reservation->complete();

        $this->assertEquals('test@gmail.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(300, $order->charged_amount);


    }
}
