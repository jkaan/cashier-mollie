<?php

namespace Fitblocks\Cashier\Tests\SubscriptionBuilder;

use Fitblocks\Cashier\Coupon\BaseCouponHandler;
use Fitblocks\Cashier\Coupon\Contracts\AcceptsCoupons;
use Fitblocks\Cashier\Coupon\Coupon;
use Fitblocks\Cashier\Coupon\RedeemedCoupon;
use Fitblocks\Cashier\Exceptions\CouponException;
use Fitblocks\Cashier\Order\OrderItemCollection;

class InvalidatingCouponHandler extends BaseCouponHandler
{
    public function validate(Coupon $coupon, AcceptsCoupons $model)
    {
        throw new CouponException('This exception should be thrown');
    }

    public function getDiscountOrderItems(?RedeemedCoupon $redeemedCoupon, OrderItemCollection $items)
    {
        return $items;
    }
}
