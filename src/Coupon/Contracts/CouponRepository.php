<?php

namespace Fitblocks\Cashier\Coupon\Contracts;

use Fitblocks\Cashier\Coupon\Coupon;
use Fitblocks\Cashier\Exceptions\CouponNotFoundException;

interface CouponRepository
{
    /**
     * @param string $coupon
     * @return Coupon|null
     */
    public function find(string $coupon);

    /**
     * @param string $coupon
     * @return Coupon
     *
     * @throws CouponNotFoundException
     */
    public function findOrFail(string $coupon);
}
