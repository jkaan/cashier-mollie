<?php

namespace Fitblocks\Cashier\Coupon;

use Illuminate\Database\Eloquent\Collection;
use Fitblocks\Cashier\Order\OrderItem;
use Fitblocks\Cashier\Order\OrderItemCollection;

class RedeemedCouponCollection extends Collection
{
    public function applyTo(OrderItem $item)
    {
        return $this->reduce(
            function(OrderItemCollection $carry, RedeemedCoupon $coupon) {
                return $coupon->applyTo($carry);
            },
            $item->toCollection()
        );
    }
}
