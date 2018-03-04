<?php

namespace App\Http\Controllers;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGatewayInterface;
use App\Concert;
use App\Order;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
    private $paymentgateway;

    public function __construct(PaymentGatewayInterface $paymentgateway)
    {
        $this->paymentgateway = $paymentgateway;
    }


    /**
     * Store a newly created resource in storage.
     * Create Order and make charge
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);
        $this->validate($request, [
            'email' => 'required|email',
            'ticket_quantity' => 'required|numeric|min:1',
            'payment_token' => 'required',
        ]);

    try
        {
            // charge the customer for the tickets
            //$amount = ticket_quantity * concert price

            $this->paymentgateway->charge($request['ticket_quantity'] * $concert->price, $request['payment_token']);


            /**
             *
             *
             *** Before Refactoring ***
             *
             * // create order
             * $order = $concert->orders()->create(['email'=>$request['email']]);
             *
             * // create tickets for the order
             * foreach (range(1, $request['ticket_quantity']) as $i)
             * {
             * $order->tickets()->create([]);
             * }
             *
             */

            /**
             * **** After Refactoring
             */

            $concert->orderTickets($request['email'], $request['ticket_quantity']);

            return response()->json([], 201);
        }
    catch (PaymentFailedException $x)
        {
            return response()->json([], 422);
        }
    }



}
