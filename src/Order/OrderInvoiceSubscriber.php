<?php

namespace Fitblocks\Cashier\Order;

use Illuminate\Support\Facades\Event;
use Fitblocks\Cashier\Events\FirstPaymentPaid;
use Fitblocks\Cashier\Events\OrderInvoiceAvailable;
use Fitblocks\Cashier\Events\OrderPaymentPaid;

class OrderInvoiceSubscriber
{
    /**
     * @param FirstPaymentPaid $event
     */
    public function handleFirstPaymentPaid($event)
    {
        Event::dispatch(new OrderInvoiceAvailable($event->order));
    }

    /**
     * @param OrderPaymentPaid $event
     */
    public function handleOrderPaymentPaid($event)
    {
        Event::dispatch(new OrderInvoiceAvailable($event->order));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            OrderPaymentPaid::class,
            self::class . '@handleOrderPaymentPaid'
        );

        $events->listen(
            FirstPaymentPaid::class,
            self::class . '@handleFirstPaymentPaid'
        );
    }
}
