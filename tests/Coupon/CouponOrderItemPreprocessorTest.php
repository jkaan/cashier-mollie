<?php

namespace Fitblocks\Cashier\Tests\Coupon;

use Fitblocks\Cashier\Coupon\AppliedCoupon;
use Fitblocks\Cashier\Coupon\Contracts\CouponRepository;
use Fitblocks\Cashier\Coupon\CouponOrderItemPreprocessor;
use Fitblocks\Cashier\Coupon\RedeemedCoupon;
use Fitblocks\Cashier\Order\OrderItem;
use Fitblocks\Cashier\Order\OrderItemCollection;
use Fitblocks\Cashier\Subscription;
use Fitblocks\Cashier\Tests\BaseTestCase;

class CouponOrderItemPreprocessorTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withPackageMigrations();
    }

    /** @test */
    public function appliesCoupon()
    {
        $this->withMockedCouponRepository();

        /** @var Subscription $subscription */
        $subscription = factory(Subscription::class)->create();
        $item = factory(OrderItem::class)->make();
        $subscription->orderItems()->save($item);

        /** @var \Fitblocks\Cashier\Coupon\Coupon $coupon */
        $coupon = app()->make(CouponRepository::class)->findOrFail('test-coupon');
        $coupon->redeemFor($subscription);
        $preprocessor = new CouponOrderItemPreprocessor();
        $this->assertEquals(0, AppliedCoupon::count());

        $result = $preprocessor->handle($item->toCollection());

        $this->assertEquals(1, AppliedCoupon::count());
        $this->assertInstanceOf(OrderItemCollection::class, $result);
        $this->assertNotEquals($item->toCollection(), $result);
    }

    /** @test */
    public function passesThroughWhenNoRedeemedCoupon()
    {
        $preprocessor = new CouponOrderItemPreprocessor();
        $items = factory(OrderItem::class, 1)->make();
        $this->assertInstanceOf(OrderItemCollection::class, $items);
        $this->assertEquals(0, RedeemedCoupon::count());

        $result = $preprocessor->handle($items);

        $this->assertInstanceOf(OrderItemCollection::class, $result);
        $this->assertEquals($items, $result);
    }
}
