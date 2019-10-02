<?php

namespace Fitblocks\Cashier\Order;

class PersistOrderItemsPreprocessor extends BaseOrderItemPreprocessor
{
    /**
     * @param \Fitblocks\Cashier\Order\OrderItemCollection $items
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    public function handle(OrderItemCollection $items)
    {
        return $items->save();
    }
}
