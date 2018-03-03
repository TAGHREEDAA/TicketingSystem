<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\FakePaymentGatewy;
use App\Billing\PaymentGatewayInterface;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected function SetUp(){

        parent::setUp();

        // put any shared work // arrange

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGatewayInterface::class, $this->paymentGateway);

        $this->concert = factory(Concert::class)->create();

    }

    private function orderTicketsHelper($concert, $params)
    {
        $response = $this->json('POST', '/concerts/'.$concert->id.'/orders',$params);
        return $response;
    }

    private function assertValidationError($fieldName , $response)
    {
        // assert correct status
        $response->assertStatus(422);
        // assert we find related error
        $this->assertArrayHasKey($fieldName, $response->decodeResponseJson()['errors']);
    }




    /** @test  */
    public function CustomerCanPurchaseTicket()
    {
        // Arrange
        // create a concert
        $this->concert = factory(Concert::class)->create(['price'=> 100]);

//        $paymentGateway = new FakePaymentGateway;
//        $this->app->instance(PaymentGatewayInterface::class, $paymentGateway);

        // Act [End point test]
        // Customer Purchase the concert tickets

        /* Before refactoring
        $response = $this->json('POST', '/concerts/'.$this->concert->id.'/orders',[

            'email' => 'test@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        */

        $response = $this->orderTicketsHelper($this->concert, [
            'email' => 'test@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        

        // Assert
        // 1- The request success
        //MakesHttpRequests.php
        $response->assertStatus(201); // create response code

        // 2- Customer charged the correct amount
        $this->assertEquals(300, $this->paymentGateway->totalCharges());

        // 3- The Order exist for the customer
        $order = $this->concert->orders()->where('email','test@gmail.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());

/*
        $this->assertTrue($concert->orders->contains(function ($order){
            return $order->email == 'test@gmail.com';
        }));
*/
    }


    /** @test  */
    public function emailIsRequiredToPurchaseTickets()
    {
        //$this->disableExceptionHandling();

//        $concert = factory(Concert::class)->create();

//        $paymentGateway = new FakePaymentGateway();
//        $this->app->instance(PaymentGatewayInterface::class, $paymentGateway);

/* Before refactoring
        $response = $this->json('POST', 'concerts/'.$this->concert->id.'/orders',[
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        */

        $response = $this->orderTicketsHelper($this->concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

//        $response->assertStatus(422);

//        $this->assertArrayHasKey('email', $response->decodeResponseJson()['errors']);

        $this->assertValidationError('email', $response);

    }


    /** @test  */
    public function emailIsValidToPurchaseTickets()
    {
//        $this->disableExceptionHandling();

//        $concert = factory(Concert::class)->create();

        /* Before Refactoring
        $response = $this->json('POST', 'concerts/'.$this->concert->id.'/orders',[
            'email' => 'not-valid-email',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        */

        $response = $this->orderTicketsHelper($this->concert, [
            'email' => 'not-valid-email',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);


//        $response->assertStatus(422);

//        $this->assertArrayHasKey('email', $response->decodeResponseJson()['errors']);
        $this->assertValidationError('email', $response);
    }

    /** @test */
    public function ticketQuantityIsRequired()
    {
        // -we have a concert $this->concert

        // act purchase tickets without sent quantity

        /* Before Factoring
        $response = $this->json('POST', 'concerts/'.$this->concert->id.'/orders',[
            'email' => 'test@gmail.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        */

        $response = $this->orderTicketsHelper($this->concert,[
            'email' => 'test@gmail.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);


        // assert status code
//        $response->assertStatus(422);

        // assert the correct error message
//        $this->assertArrayHasKey('ticket_quantity', $response->decodeResponseJson()['errors']);

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test */
    public function ticketQuantityAtLeastOne()
    {
        // we have a concert

//        $this->disableExceptionHandling();
        // make a request with 0 or - num of tickets

        /* Before Factoring
        $response =$this->json('POST','concerts/'.$this->concert->id.'/orders' ,[
            'email' => 'test@gmail.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        */

        $response =$this->orderTicketsHelper($this->concert ,[
            'email' => 'test@gmail.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

//        $response->assertStatus(422);

//        $this->assertArrayHasKey('ticket_quantity', $response->decodeResponseJson()['errors']);

        $this->assertValidationError('ticket_quantity', $response);
    }


    /** @test */
    public function paymentTokenIsRequired()
    {
//        $this->disableExceptionHandling();
        // we have a concert

        // make a request without payment Token

        /* Before Factoring
        $response = $this->json('POST', 'concerts/'.$this->concert->id.'/orders',[
            'email' => 'test@gmail.com',
            'ticket_quantity' => 3
        ]);
        */

        $response = $this->orderTicketsHelper($this->concert, [
            'email' => 'test@gmail.com',
            'ticket_quantity' => 3
        ]);

        // assert correct status
//        $response->assertStatus(422);

        // assert we find related error
//        $this->assertArrayHasKey('payment_token', $response->decodeResponseJson()['errors']);

        $this->assertValidationError('payment_token' , $response);

    }
}
