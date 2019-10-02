<?php

namespace Fitblocks\Cashier\Events;

use Illuminate\Queue\SerializesModels;
use Fitblocks\Cashier\Order\Order;

class OrderProcessed
{
    use SerializesModels;

    /**
     * The processed order.
     *
     * @var Order
     */
    public $order;

    /**
     * OrderProcessed constructor.
     *
     * @param \Fitblocks\Cashier\Order\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
