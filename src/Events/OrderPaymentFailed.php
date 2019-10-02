<?php

namespace Fitblocks\Cashier\Events;

use Illuminate\Queue\SerializesModels;

class OrderPaymentFailed
{
    use SerializesModels;

    /**
     * The failed order.
     *
     * @var \Fitblocks\Cashier\Order\Order
     */
    public $order;

    /**
     * Creates a new OrderPaymentFailed event.
     *
     * @param $order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }
}
