<?php

namespace App\Http\Controllers;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGatewayInterface;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
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

        try {

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

            // 1- find tickets
            $tickets = $concert->findTickets($request['ticket_quantity']);

            // 2- charge the customer
//            $order = $concert->orderTickets($request['email'], $request['ticket_quantity']);
            $this->paymentgateway->charge($request['ticket_quantity'] * $concert->price, $request['payment_token']);

            // 3- create an order for the tickets
//            $order = $concert->orderTickets($request['email'], $tickets);


//            $order =  $concert->createOrder($request['email'], $tickets);

            // charge the customer for the tickets
            //$amount = ticket_quantity * concert price
//
//                return response()->json([
//                    'id' => $order->id,
//                    'email' => $order->email,
//                    'ticket_quantity' => $order->ticketQuantity(),
//                    'charged_amount' => $this->paymentgateway->totalCharges(),
//                ], 201);

            return response()->json(Order::forTickets($tickets, $request['email']), 201);
        }
        catch (PaymentFailedException $e)
            {
                // cancelling failed order
//                $order->cancel(); because we refactor the design and make it not creating the order untill payment is successful
                return response()->json([], 422);
            }
        catch (NotEnoughTicketsException $e)
            {
                return response()->json([], 422);
            }

    }



}
