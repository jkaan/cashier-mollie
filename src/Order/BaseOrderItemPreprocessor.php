<?php

namespace Fitblocks\Cashier\Order;

abstract class BaseOrderItemPreprocessor
{
    /**
     * @param \Fitblocks\Cashier\Order\OrderItemCollection $items
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    abstract public function handle(OrderItemCollection $items);
}
