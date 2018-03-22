<?php

namespace App\Http\Controllers;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGatewayInterface;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Reservation;
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

            // 1- find tickets
//            $tickets = $concert->findTickets($request['ticket_quantity']);
//            $tickets = $concert->reserveTickets($request['ticket_quantity']);

            $reservation = $concert->reserveTickets($request['ticket_quantity'], $request['email']);

            // 2- charge the customer
            $this->paymentgateway->charge($reservation->totalCost(), $request['payment_token']);

            // 3- create an order for the tickets
//            return response()->json(Order::forTickets($reservation->tickets(), $reservation->email(), $reservation->totalCost()), 201);
            return response()->json($reservation->complete(), 201);
        }
        catch (PaymentFailedException $e)
            {
                $reservation->cancel();
                return response()->json([], 422);
            }
        catch (NotEnoughTicketsException $e)
            {
                return response()->json([], 422);
            }

    }



}
