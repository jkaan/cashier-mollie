<?php

namespace Fitblocks\Cashier\Events;

use Fitblocks\Cashier\Coupon\AppliedCoupon;
use Fitblocks\Cashier\Coupon\RedeemedCoupon;

class CouponApplied
{
    /**
     * @var \Fitblocks\Cashier\Coupon\RedeemedCoupon
     */
    public $redeemedCoupon;

    /**
     * @var \Fitblocks\Cashier\Coupon\AppliedCoupon
     */
    public $appliedCoupon;

    public function __construct(RedeemedCoupon $redeemedCoupon, AppliedCoupon $appliedCoupon)
    {
        $this->redeemedCoupon = $redeemedCoupon;
        $this->appliedCoupon = $appliedCoupon;
    }
}
