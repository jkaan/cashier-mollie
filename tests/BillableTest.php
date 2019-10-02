<?php

namespace Fitblocks\Cashier\Tests;

use Fitblocks\Cashier\SubscriptionBuilder\FirstPaymentSubscriptionBuilder;
use Fitblocks\Cashier\SubscriptionBuilder\MandatedSubscriptionBuilder;
use Fitblocks\Cashier\Tests\Fixtures\User;

class BillableTest extends BaseTestCase
{
    /** @test */
    public function testTaxPercentage()
    {
        $this->withPackageMigrations();
        $user = factory(User::class)->create([
            'tax_percentage' => 21.5,
        ]);

        $this->assertEquals(21.5, $user->taxPercentage());
    }

    /** @test */
    public function returnsFirstPaymentSubscriptionBuilderIfMandateIdOnOwnerIsNull()
    {
        $this->withConfiguredPlans();
        $user = $this->getUser(false, ['mollie_mandate_id' => null]);

        $builder = $user->newSubscription('default', 'monthly-10-1');

        $this->assertInstanceOf(FirstPaymentSubscriptionBuilder::class, $builder);
    }

    /** @test */
    public function returnsFirstPaymentSubscriptionBuilderIfOwnerMandateIsInvalid()
    {
        $this->withConfiguredPlans();
        $this->withPackageMigrations();

        $revokedMandateId = 'mdt_MvfK2PRzNJ';

        $user = $this->getUser(false, ['mollie_mandate_id' => $revokedMandateId]);

        $builder = $user->newSubscription('default', 'monthly-10-1');

        $this->assertInstanceOf(FirstPaymentSubscriptionBuilder::class, $builder);
    }

    /** @test */
    public function returnsDefaultSubscriptionBuilderIfOwnerHasValidMandateId()
    {
        $this->withConfiguredPlans();
        $user = $this->getMandatedUser(false);

        $builder = $user->newSubscription('default', 'monthly-10-1');

        $this->assertInstanceOf(MandatedSubscriptionBuilder::class, $builder);
    }
}
