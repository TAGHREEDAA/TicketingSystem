<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
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
        $paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGatewayInterface::class, $paymentGateway);


        // Act --> charge an amount with a valid token
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());



        // Assert --> the total charges by the gateway == 2500
        $this->assertEquals(2500, $paymentGateway->totalCharges());
        
    }

    /** @test */
//     * @expectedException /App/Billing/PaymentFailedException
    public function ChargesWithInvalidTokenFail()
    {
        try {
            // Arrange
            $paymentGateway = new FakePaymentGateway();
            $this->app->instance(PaymentGatewayInterface::class, $paymentGateway);

            // Act -- make charge with invalid token
            $paymentGateway->charge(2500, 'invalid-token');
        }
        catch (PaymentFailedException $e)
        {
//            $this->assertEquals(2500, $e->failedChargeAmount);
            return;
        }

        // will fail the test if the exception doesn't thrown
        $this->fail();
    }
    
    
    /** @test */
    public function RunHookBeforeFirstCharge()
    {
        $paymentGateway = new FakePaymentGateway();

        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallbackRan){
            $timesCallbackRan++;

            $paymentGateway->charge(250, $paymentGateway->getValidTestToken());

            // to assert that call back called first
            $this->assertEquals(250, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(250, $paymentGateway->getValidTestToken());

        $this->assertEquals(500, $paymentGateway->totalCharges());
        $this->assertEquals(1 , $timesCallbackRan);

    }
}
