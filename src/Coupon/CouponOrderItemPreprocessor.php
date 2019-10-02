<?php

namespace Fitblocks\Cashier\Coupon;

use Fitblocks\Cashier\Order\BaseOrderItemPreprocessor;
use Fitblocks\Cashier\Order\OrderItem;
use Fitblocks\Cashier\Order\OrderItemCollection;

class CouponOrderItemPreprocessor extends BaseOrderItemPreprocessor
{
    /**
     * @param \Fitblocks\Cashier\Order\OrderItemCollection $items
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    public function handle(OrderItemCollection $items)
    {
        $result = new OrderItemCollection;

        $items->each(function (OrderItem $item) use (&$result) {
            if($item->orderableIsSet()) {
                $coupons = $this->getActiveCoupons($item->orderable_type, $item->orderable_id);
                $result = $result->concat($coupons->applyTo($item));
            } else {
                $result->push($item);
            }
        });

        return $result;
    }

    /**
     * @param $modelType
     * @param $modelId
     * @return mixed
     */
    protected function getActiveCoupons($modelType, $modelId)
    {
        return RedeemedCoupon::whereModel($modelType, $modelId)->active()->get();
    }
}
