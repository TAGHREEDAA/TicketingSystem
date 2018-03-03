<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGatewayInterface;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */

    public function ValidTokenChargesAreSuccessful()
    {
        // Arrange --> create a paymentgateway object
        $paymentgateway = new FakePaymentGateway;
        $this->app->instance(PaymentGatewayInterface::class, $paymentgateway);


        // Act --> charge an amount with a valid token
        $paymentgateway->charge(2500, $paymentgateway->getValidTestToken());



        // Assert --> the total charges by the gateway == 2500
        $this->assertEquals(2500, $paymentgateway->totalCharges());
        
    }
}
