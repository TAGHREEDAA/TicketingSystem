<?php

namespace Tests\Unit;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ConcertTest
 * @package Tests\Unit
 */
class ConcertTest extends TestCase
{

    // commented because we don't use create we replaced it with make()
    use DatabaseMigrations;


    /** @test */
    public function testFormattedDate(){
        // 1- Arrange
        // create a concert with specific date
         $concert = factory(Concert::class)->make([
            'datetime'=>Carbon::parse('2018-01-01 8:00am')
        ]);

        // 2- Act
        // get the FormattedDate
        //$date = $concert->FormattedDate;


        // 3- Assert
        // Assert equals certain value
        // or assert format like

        $this->assertEquals('January 1, 2018', $concert->FormattedDate);
    }


    /** @test  */
    public function testFormattedTime()
    {
        $concert = factory(Concert::class)->make([
            'datetime' => Carbon::parse('2018-01-01 17:00:00')
        ]);

        $this->assertEquals('5:00 pm', $concert->FormattedTime);
    }

    /** @test  */
    public function testDollarsPrice()
    {
        $concert = factory(Concert::class)->make([
           'price' => 130.55
        ]);

        $this->assertEquals('130.55 $', $concert->DollarsPrice);
    }

    /** @test */
    public function testPublishedConcerts()
    {
        //$publishedConcertA = factory(Concert::class)->create(['published_at' => Carbon::parse('-2 weeks')]);
        $publishedConcertA = factory(Concert::class)->states('published')->create();

//        $publishedConcertB = factory(Concert::class)->create(['published_at' => Carbon::parse('-2 days')]);
        $publishedConcertB = factory(Concert::class)->states('published')->create();

//        $unPublishedConcert = factory(Concert::class)->create(['published_at' => null]);
        $unPublishedConcert = factory(Concert::class)->states('unpublished')->create();
        
        $allPublishedConcerts = Concert::published()->get() ;



        $this->assertTrue($allPublishedConcerts->contains($publishedConcertA));
        $this->assertTrue($allPublishedConcerts->contains($publishedConcertB));
        $this->assertFalse($allPublishedConcerts->contains($unPublishedConcert));
    }


    /** @test */
    public function CanOrderTickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);

        $order = $concert->orderTickets('test@gmail.com', 3);


        $this->assertEquals('test@gmail.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());

    }

    /** @test */
    public function canAddTickets()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);

        // assert tickets created
        // assert total remaining tickets
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function testTicketsRemainingNotIncludeOrdered()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);
        $concert->orderTickets('test@gmail.com', 10);
        $this->assertEquals(40, $concert->ticketsRemaining());
    }


    /** @test */
    public function PurchaseMoreThanRemainTicketsThrowException()
    {
        $this->disableExceptionHandling();
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        try{
            $concert->orderTickets('test@gmail.com', 11);
        }
        catch (NotEnoughTicketsException $e)
        {
            // no order created for the email
            $this->assertNull($concert->orders()->where('email','test@gmail.com')->first());
            // the customer didn't charge ---> in the purchaseFeature test not here

            // the tickets remaining 
            $this->assertEquals(10, $concert->ticketsRemaining());
            return; // so the test doesn't fail
        }
        $this->fail('Order was successfully created even though no enough tickets!!!');
    }
    
    
    /** @test */
    public function CanNotOrderTicketsAlreadyPurchased()
    {
        // 1- Arrange -- create concert / add tickets / order some of them
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);
        $concert->orderTickets('test@gmail.com', 7);

        // 2- Act -- another email order tickets more then remaining
        try {
            $concert->orderTickets('user@gmail.com', 5);
        }
        catch (NotEnoughTicketsException $e)
        {
            // 3- Assert
            // user order == null

            $this->assertNull($concert->orders()->where('email','user@gmail.com')->first());
            // the remaining tickets = 3
            $this->assertEquals(3, $concert->ticketsRemaining());

            return;
        }

        $this->fail('Order was successfully created even though some tickets are purchased before!!!');

    }
}



