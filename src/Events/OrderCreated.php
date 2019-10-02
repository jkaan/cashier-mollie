<?php

namespace Fitblocks\Cashier\Events;

use Illuminate\Queue\SerializesModels;
use Fitblocks\Cashier\Order\Order;

class OrderCreated
{
    use SerializesModels;

    /**
     * The created order.
     *
     * @var Order
     */
    public $order;

    /**
     * Creates a new OrderCreated event.
     *
     * @param $order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }
}
