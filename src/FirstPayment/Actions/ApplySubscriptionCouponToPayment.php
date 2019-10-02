<?php

namespace Fitblocks\Cashier\FirstPayment\Actions;

use Illuminate\Database\Eloquent\Model;
use Fitblocks\Cashier\Coupon\Coupon;
use Fitblocks\Cashier\Order\OrderItemCollection;

class ApplySubscriptionCouponToPayment extends BaseNullAction
{
    /**
     * @var \Fitblocks\Cashier\Coupon\Coupon
     */
    protected $coupon;

    /**
     * The coupon's (discount) OrderItems
     * @var \Fitblocks\Cashier\Order\OrderItemCollection
     */
    protected $orderItems;

    /**
     * ApplySubscriptionCouponToPayment constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $owner
     * @param \Fitblocks\Cashier\Coupon\Coupon $coupon
     * @param \Fitblocks\Cashier\Order\OrderItemCollection $orderItems
     */
    public function __construct(Model $owner, Coupon $coupon, OrderItemCollection $orderItems)
    {
        $this->owner = $owner;
        $this->coupon = $coupon;
        $this->orderItems = $this->coupon->handler()->getDiscountOrderItems(null, $orderItems);
    }

    /**
     * @return \Money\Money
     */
    public function getSubtotal()
    {
        return $this->toMoney($this->orderItems->sum('subtotal'));
    }

    /**
     * @return \Money\Money
     */
    public function getTax()
    {
        return $this->toMoney($this->orderItems->sum('tax'));
    }

    /**
     * @param int $value
     * @return \Money\Money
     */
    protected function toMoney($value = 0)
    {
        return money($value, $this->getCurrency());
    }
}
