<?php

namespace Tests\Unit;

use App\Concert;
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



}



