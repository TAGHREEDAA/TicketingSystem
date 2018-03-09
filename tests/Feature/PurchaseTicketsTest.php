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

    protected  $paymentGateway;
    protected $concert;

    protected function SetUp(){

        parent::setUp();

        // put any shared work // arrange

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGatewayInterface::class, $this->paymentGateway);

        $this->concert = factory(Concert::class)->states('published')->create();

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
    public function CustomerCanPurchasePublishedConcertTicket()
    {
        $this->disableExceptionHandling();
        // Arrange
        // create a concert
        $this->concert = factory(Concert::class)->states('published')->create(['price'=> 100]);

        $this->concert->addTickets(3);
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


        // JSON assertions to get meaningful messages
        $response->assertJsonFragment([
            'email' => 'test@gmail.com',
            'ticket_quantity' => 3,
            'charged_amount' => 300,
        ]);

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

    /** @test */
    public function CanNotPurchaseUnpublishedConcertTickets()
    {
//        $this->disableExceptionHandling();
        // create unpublished  concert
        $this->concert = factory(Concert::class)->states('unpublished')->create();
        $this->concert->addTickets(3);

        // Act -- charge tickets
        $response = $this->orderTicketsHelper($this->concert, [
           'email' => 'test@gmail.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        // Assertions
        // 1- 404 Not found because the concert is supposed to be hidden
        $response->assertStatus(404);

        // 2- assert no orders for the concert
        $this->assertEquals(0, $this->concert->orders()->count());

        // 3- make sure the customer is not actually charged
        $this->assertEquals(0, $this->paymentGateway->totalCharges());


    }


    /** @test */
    public function CanNotOrderMoreThanRemainTickets()
    {
        $this->disableExceptionHandling();
        // arrange -- create a published concert
        // $this->concert

        $this->concert->addTickets(50);


        // act -- order tickets more than the concert total tickets
        $response = $this->orderTicketsHelper($this->concert, [
            'email' => 'test@gmail.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        // Assertions
        // 1- response status 422
        // 2- no order created with the email test@gmail.com
        // 3- no charge on customer
        // 4- the tickets number of the concert remains as it was


        $response->assertStatus(422);
        $this->assertNull($this->concert->orders()->where('email','test@gmail.com')->first());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $this->concert->ticketsRemaining());

    }
    /** @test */
    public function OrderNotCreatedIfPaymentFail()
    {
        $this->disableExceptionHandling();
        // arrang -- need a concert
        $this->concert->addTickets(3);

        // act -- json request to order ticket
        $response = $this->orderTicketsHelper($this->concert, [
            'email' => 'test@gmail.com',
            'ticket_quantity' =>3 ,
            'payment_token' => 'invalid-payment-token',
        ]);

        // assertions

        // 1- status code
        /*http://www.restapitutorial.com/httpstatuscodes.html*/
        // 402 == payment required
        // 422 Unprocessable Entity (WebDAV)

        $response->assertStatus(422);

        // 2- order is null
        $this->assertNull($this->concert->orders()->where('email','test@gmail.com')->first());

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
