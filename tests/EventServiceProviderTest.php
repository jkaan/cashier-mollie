<?php

namespace Fitblocks\Cashier\Tests;

use Illuminate\Support\Facades\Event;
use Fitblocks\Cashier\Events\OrderInvoiceAvailable;
use Fitblocks\Cashier\Events\OrderPaymentPaid;
use Fitblocks\Cashier\Order\Order;

class EventServiceProviderTest extends BaseTestCase
{
    /** @test */
    public function itIsWiredUpAndFiring()
    {
        Event::fake(OrderInvoiceAvailable::class);

        $event = new OrderPaymentPaid(factory(Order::class)->make());
        Event::dispatch($event);

        Event::assertDispatched(OrderInvoiceAvailable::class, function($e) use ($event) {
            return $e->order === $event->order;
        });
    }
}
