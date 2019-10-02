<?php

namespace Fitblocks\Cashier\Tests\Order;

use Illuminate\Support\Facades\Event;
use Fitblocks\Cashier\Events\FirstPaymentPaid;
use Fitblocks\Cashier\Events\OrderInvoiceAvailable;
use Fitblocks\Cashier\Events\OrderPaymentPaid;
use Fitblocks\Cashier\Order\Order;
use Fitblocks\Cashier\Order\OrderInvoiceSubscriber;
use Fitblocks\Cashier\Tests\BaseTestCase;
use Mollie\Api\Resources\Payment;

class OrderInvoiceSubscriberTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriber = new OrderInvoiceSubscriber;
    }

    /** @test */
    public function itHandlesTheFirstPaymentPaidEvent()
    {
        $this->assertItHandlesEvent(
            new FirstPaymentPaid($this->mock(Payment::class), $this->order()),
            'handleFirstPaymentPaid'
        );
    }

    /** @test */
    public function itHandlesTheOrderPaymentPaidEvent()
    {
        $this->assertItHandlesEvent(
            new OrderPaymentPaid($this->order()),
            'handleOrderPaymentPaid'
        );
    }

    private function assertItHandlesEvent($event, $methodName)
    {
        Event::fake(OrderInvoiceAvailable::class);

        (new OrderInvoiceSubscriber)->$methodName($event);

        Event::assertDispatched(OrderInvoiceAvailable::class, function($e) use ($event) {
            return $e->order === $event->order;
        });
    }

    private function order() {
        return factory(Order::class)->make();
    }
}
