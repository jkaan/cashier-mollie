<?php

namespace Fitblocks\Cashier\Tests\Coupon;

use Fitblocks\Cashier\Coupon\ConfigCouponRepository;
use Fitblocks\Cashier\Coupon\Contracts\CouponRepository;
use Fitblocks\Cashier\Coupon\Coupon;
use Fitblocks\Cashier\Exceptions\CouponNotFoundException;
use Fitblocks\Cashier\Tests\BaseTestCase;

class ConfigCouponRepositoryTest extends BaseTestCase
{
    /** @var ConfigCouponRepository */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $defaults = [
            'handler' => '\NonExistentHandler',
            'times' => 6,
            'context' => [
                'foo' => 'bar',
            ],
        ];
        $coupons = [
            'test-coupon' => [
                'handler' => \Fitblocks\Cashier\Coupon\FixedDiscountHandler::class,
                'context' => [
                    'description' => 'Welcome to '.config('app.name'),
                    'discount' => [
                        'currency' => 'EUR',
                        'value' => '5.00',
                    ],
                ],
            ],
        ];
        $this->repository = new ConfigCouponRepository($defaults, $coupons);
    }

    /** @test */
    public function ItIsContainerBound()
    {
        $repository = app()->make(CouponRepository::class);
        $this->assertInstanceOf(ConfigCouponRepository::class, $repository);
    }

    /** @test */
    public function findReturnsNullWhenNotFound()
    {
        $this->assertNull($this->repository->find('some_wrong_name'));
    }

    /** @test */
    public function findReturnsCouponWhenFound()
    {
        $this->assertInstanceOf(Coupon::class, $this->repository->find('test-coupon'));
    }

    /** @test */
    public function findOrFailCorrect()
    {
        $this->assertInstanceOf(Coupon::class, $this->repository->findOrFail('test-coupon'));
    }

    /** @test */
    public function findOrFailWrong()
    {
        $this->expectException(CouponNotFoundException::class);
        $this->repository->findOrFail('some_wrong_name');
    }

    /** @test */
    public function findOrFailIsCaseInsensitive()
    {
        $lowercaseCoupon = $this->repository->find('test-coupon');
        $uppercaseCoupon = $this->repository->find('TEST-COUPON');
        $this->assertInstanceOf(Coupon::class, $lowercaseCoupon);
        $this->assertInstanceOf(Coupon::class, $uppercaseCoupon);
        $this->assertEquals($lowercaseCoupon, $uppercaseCoupon);
    }

}
