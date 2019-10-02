<?php

namespace Fitblocks\Cashier\Order\Contracts;

use Fitblocks\Cashier\Order\OrderItem;

interface PreprocessesOrderItems
{
    /**
     * Called right before processing the order item into an order.
     *
     * @param OrderItem $item
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    public static function preprocessOrderItem(OrderItem $item);
}
