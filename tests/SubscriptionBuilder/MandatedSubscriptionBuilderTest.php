<?php

namespace Fitblocks\Cashier\Tests\SubscriptionBuilder;

use Carbon\Carbon;
use Fitblocks\Cashier\Coupon\AppliedCoupon;
use Fitblocks\Cashier\Coupon\RedeemedCoupon;
use Fitblocks\Cashier\Exceptions\CouponException;
use Fitblocks\Cashier\SubscriptionBuilder\MandatedSubscriptionBuilder;
use Fitblocks\Cashier\Tests\BaseTestCase;

class MandatedSubscriptionBuilderTest extends BaseTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withPackageMigrations();
        $this->withConfiguredPlans();
        $this->user = $this->getCustomerUser(true);
    }

    /** @test */
    public function testWithCouponNoTrial()
    {
        $this->withMockedCouponRepository();
        $now = Carbon::parse('2019-01-01');
        $this->withTestNow($now);

        $this->assertEquals(0, RedeemedCoupon::count());
        $this->assertEquals(0, AppliedCoupon::count());

        $builder = $this->getBuilder();

        $subscription = $builder->withCoupon('test-coupon')->create();

        $this->assertEquals(1, $subscription->redeemedCoupons()->count());

        // Coupons will be applied when (pre)processing the Subscription OrderItems
        $this->assertEquals(0, $subscription->appliedCoupons()->count());

        $orderItem = $subscription->orderItems()->first();
        $this->assertCarbon($now, $orderItem->process_at);
        $this->assertEquals('EUR', $orderItem->currency);
        $this->assertEquals(1000, $orderItem->unit_price);
        $this->assertEquals(1, $orderItem->quantity);
    }

    public function testWithCouponAndTrial()
    {
        $this->withMockedCouponRepository();
        $now = Carbon::parse('2019-01-01');
        $this->withTestNow($now);

        $this->assertEquals(0, RedeemedCoupon::count());
        $this->assertEquals(0, AppliedCoupon::count());

        $builder = $this->getBuilder();

        $subscription = $builder
            ->withCoupon('test-coupon')
            ->trialDays(5)
            ->create();

        $this->assertEquals(1, $subscription->redeemedCoupons()->count());

        // Coupons will be applied when (pre)processing the Subscription OrderItems
        $this->assertEquals(0, $subscription->appliedCoupons()->count());

        $orderItem = $subscription->orderItems()->first();
        $this->assertCarbon($now->copy()->addDays(5), $orderItem->process_at);
        $this->assertEquals('EUR', $orderItem->currency);
        $this->assertEquals(1000, $orderItem->unit_price);
        $this->assertEquals(1, $orderItem->quantity);
    }

    /** @test */
    public function testWithCouponValidatesCoupon()
    {
        $this->expectException(CouponException::class);
        $this->withMockedCouponRepository(null, new InvalidatingCouponHandler);
        $this->getBuilder()->withCoupon('test-coupon')->create();
    }

    /**
     * @return \Fitblocks\Cashier\SubscriptionBuilder\MandatedSubscriptionBuilder
     */
    protected function getBuilder()
    {
        return new MandatedSubscriptionBuilder(
            $this->user,
            'default',
            'monthly-10-1'
        );
    }
}
