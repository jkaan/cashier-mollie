<?php

namespace Fitblocks\Cashier\Tests\Order;

use Illuminate\Support\Arr;
use Fitblocks\Cashier\Order\BaseOrderItemPreprocessor;
use Fitblocks\Cashier\Order\OrderItem;
use Fitblocks\Cashier\Order\OrderItemCollection;
use Fitblocks\Cashier\Tests\BaseTestCase;

class FakeOrderItemPreprocessor extends BaseOrderItemPreprocessor {

    protected $items = [];
    protected $result;

    /**
     * @param \Fitblocks\Cashier\Order\OrderItemCollection $items
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    public function handle(OrderItemCollection $items)
    {
        $this->items[] = $items;

        return $this->result ?: $items;
    }

    public function withResult(OrderItemCollection $mockResult)
    {
        $this->result = $mockResult;

        return $this;
    }

    public function assertOrderItemHandled(OrderItem $item)
    {
        BaseTestCase::assertContains($item, Arr::flatten($this->items), "OrderItem `{$item->description}` was not handled");
    }
}
