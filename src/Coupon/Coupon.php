<?php

namespace Fitblocks\Cashier\Coupon;

use Fitblocks\Cashier\Coupon\Contracts\AcceptsCoupons;
use Fitblocks\Cashier\Coupon\Contracts\CouponHandler;
use Fitblocks\Cashier\Order\OrderItemCollection;

class Coupon
{
    /** @var string */
    protected $name;

    /** @var \Fitblocks\Cashier\Coupon\Contracts\CouponHandler */
    protected $handler;

    /** @var array */
    protected $context;

    /** @var int The number of times this coupon should be applied */
    protected $times = 1;

    /**
     * Coupon constructor.
     *
     * @param string $name
     * @param \Fitblocks\Cashier\Coupon\Contracts\CouponHandler $handler
     * @param array $context
     */
    public function __construct(string $name, CouponHandler $handler, array $context = [])
    {
        $this->name = $name;
        $this->context = $context;
        $this->handler = $handler;
        $this->handler->withContext($context);
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return \Fitblocks\Cashier\Coupon\Contracts\CouponHandler
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * @return array
     */
    public function context()
    {
        return $this->context;
    }

    /**
     * The number of times the coupon will be applied
     *
     * @return int
     */
    public function times()
    {
        return $this->times;
    }

    /**
     * @param \Fitblocks\Cashier\Coupon\Contracts\AcceptsCoupons $model
     * @return \Fitblocks\Cashier\Coupon\RedeemedCoupon
     */
    public function redeemFor(AcceptsCoupons $model)
    {
        return RedeemedCoupon::record($this, $model);
    }

    /**
     * Check if the coupon can be applied to the model
     *
     * @param \Fitblocks\Cashier\Coupon\Contracts\AcceptsCoupons $model
     * @throws \Throwable|\Fitblocks\Cashier\Exceptions\CouponException
     */
    public function validateFor(AcceptsCoupons $model)
    {
        $this->handler->validate($this, $model);
    }

    public function applyTo(RedeemedCoupon $redeemedCoupon, OrderItemCollection $items)
    {
        return $this->handler->handle($redeemedCoupon, $items);
    }
}
