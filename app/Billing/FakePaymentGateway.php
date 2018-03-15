<?php


namespace App\Billing;

class FakePaymentGateway implements PaymentGatewayInterface
{

    private $charges;
    private $beforeFirstChargeCallback;


    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken()
    {
        return 'valid-token';
        
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }

    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback != null)
        {
            $callback = $this->beforeFirstChargeCallback;

            $this->beforeFirstChargeCallback = null ; // to prevent infinite recursion

            $callback->__invoke($this);
        }

        if ($token !== $this->getValidTestToken())
        {
            throw new PaymentFailedException();
        }
        else
            $this->charges[] = $amount;
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }


}
