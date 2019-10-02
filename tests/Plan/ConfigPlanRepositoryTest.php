<?php

namespace Fitblocks\Cashier\Tests\Plan;

use Illuminate\Support\Facades\Config;
use Fitblocks\Cashier\Exceptions\PlanNotFoundException;
use Fitblocks\Cashier\Plan\ConfigPlanRepository;
use Fitblocks\Cashier\Plan\Contracts\Plan;
use Fitblocks\Cashier\Tests\BaseTestCase;

class ConfigPlanRepositoryTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set config for this runtime.
        Config::set('cashier_plans.plans', [
            'Test' => [
                'amount' => [
                    'value' => '10.00',
                    'currency' => 'EUR',
                ],
                'interval' => '1 month',
                'method' => 'directdebit',
                'description' => 'Test subscription (monthly)',
                'mandate_payment_amount' => [
                    'value' => '0.05',
                    'currency' => 'EUR',
                ],
                'mandate_payment_description' => 'Test mandate payment',
            ]
        ]);
    }

    /** @test */
    public function findReturnsNullWhenNotFound()
    {
        $this->assertNull(ConfigPlanRepository::find('some_wrong_name'));
    }

    /** @test */
    public function findReturnsPlanWhenFound()
    {
        $this->assertInstanceOf(Plan::class, ConfigPlanRepository::find('Test'));
    }

    /** @test
     * @throws \Fitblocks\Cashier\Exceptions\PlanNotFoundException
     */
    public function findOrFailCorrect()
    {
        $this->assertInstanceOf(Plan::class, ConfigPlanRepository::findOrFail('Test'));
    }

    /** @test */
    public function findOrFailWrong()
    {
        $this->expectException(PlanNotFoundException::class);
        ConfigPlanRepository::findOrFail('some_wrong_name');
    }

    /** @test */
    public function findIsCaseSensitive()
    {
        $this->assertNull(ConfigPlanRepository::find('test'));
        $this->assertInstanceOf(Plan::class, ConfigPlanRepository::find('Test'));
    }
}
