<?php

namespace Tests\Feature;


use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ViewConcertListTest
 * @package Tests\Feature
 */

class ViewConcertListTest extends TestCase
{

    use DatabaseMigrations;

    /** @test  */

    public function UserCanViewPublishedConcertList()
    {
        // 1- Arrange == Create a concert
//        $concert = factory(Concert::class)->create();
        $concert = factory(Concert::class)->states('published')->create();


        // 2- Act == View the concert
        $response = $this->get('/concerts/'. $concert->id);

        // 3- Assert == See the concert
        $response->assertSee($concert->title);
    }


    /** @test */
    public function UserCanNotViewUnpublishedConcerts()
    {
        $concert = factory(Concert::class)->create([
            'published_at' => null,
        ]);

        $response = $this->get('/concerts/'.$concert->id);

        //MakesHttpRequests.php
        $response->assertStatus(404);
    }
}
