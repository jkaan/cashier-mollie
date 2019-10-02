<?php

namespace Fitblocks\Cashier\Coupon\Contracts;

use Fitblocks\Cashier\Coupon\Coupon;
use Fitblocks\Cashier\Coupon\RedeemedCoupon;
use Fitblocks\Cashier\Exceptions\CouponException;
use Fitblocks\Cashier\Order\OrderItemCollection;

interface CouponHandler
{
    /**
     * @param array $context
     * @return \Fitblocks\Cashier\Coupon\Contracts\CouponHandler
     */
    public function withContext(array $context);

    /**
     * @param \Fitblocks\Cashier\Coupon\Coupon $coupon
     * @param \Fitblocks\Cashier\Coupon\Contracts\AcceptsCoupons $model
     * @return bool
     * @throws \Throwable|CouponException
     */
    public function validate(Coupon $coupon, AcceptsCoupons $model);

    /**
     * Apply the coupon to the OrderItemCollection
     *
     * @param \Fitblocks\Cashier\Coupon\RedeemedCoupon $redeemedCoupon
     * @param \Fitblocks\Cashier\Order\OrderItemCollection $items
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    public function handle(RedeemedCoupon $redeemedCoupon, OrderItemCollection $items);

    /**
     * @param \Fitblocks\Cashier\Coupon\RedeemedCoupon $redeemedCoupon
     * @param \Fitblocks\Cashier\Order\OrderItemCollection $items
     * @return \Fitblocks\Cashier\Order\OrderItemCollection
     */
    public function getDiscountOrderItems(?RedeemedCoupon $redeemedCoupon, OrderItemCollection $items);
}
