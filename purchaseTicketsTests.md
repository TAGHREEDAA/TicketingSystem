### `CustomerCanNotPurchaseTicketAnotherCustomerTryingToPurchase()`
        We are trying to do that to make sure person B not overrides the order and take the tickets of person A
        The steps
        
            // Find Tickets for Customer A
                                // Find Tickets for Customer B
            // Attempt to charge for A
                                // Attempt to charge for B
            // Create an Order for A
                                // Create an Order for B
   we can make use of Laravel SubRequest
   Initiate Request A
        Initiate Request B
        Finish Request B
   Finish Request B    
        
