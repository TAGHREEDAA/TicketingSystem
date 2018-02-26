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

    public function UserCanViewConcertList()
    {
        // 1- Arrange == Create a concert
        $concert = factory(Concert::class)->create();


        // 2- Act == View the concert
        $response = $this->get('/concerts/'. $concert->id);

        // 3- Assert == See the concert
        $response->assertSee($concert->title);
    }
}
