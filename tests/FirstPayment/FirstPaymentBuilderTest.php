<?php

namespace Fitblocks\Cashier\Tests\FirstPayment;

use Fitblocks\Cashier\FirstPayment\Actions\AddBalance;
use Fitblocks\Cashier\FirstPayment\FirstPaymentBuilder;
use Fitblocks\Cashier\Tests\BaseTestCase;
use Fitblocks\Cashier\Tests\Fixtures\User;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\SequenceType;

class FirstPaymentBuilderTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withPackageMigrations();
    }

    /** @test */
    public function canBuildPayload()
    {
        $owner = factory(User::class)->create();
        $this->assertEquals(0, $owner->orderItems()->count());
        $this->assertEquals(0, $owner->orders()->count());

        $builder = new FirstPaymentBuilder($owner, [
            'description' => 'Test mandate payment',
            'redirectUrl' => 'https://www.example.com',
        ]);

        $builder->inOrderTo([
            new AddBalance(
                $owner,
                money(500, 'EUR'),
                'Test add balance 1'
            ),
            new AddBalance(
                $owner,
                money(500, 'EUR'),
                'Test add balance 2'
            ),
        ]);

        $payload = $builder->getMolliePayload();
        $customerId = $payload['customerId'];
        unset($payload['customerId']);
        $check_payload = [
            'sequenceType' => SequenceType::SEQUENCETYPE_FIRST,
            'description' => 'Test mandate payment',
            'amount' => [
                'value' => '10.00',
                'currency' => 'EUR',
            ],
            'redirectUrl' => 'https://www.example.com',
            'webhookUrl' => 'https://www.example.com/mandate-webhook',
            'metadata' => [
                'owner' => [
                    'type' => get_class($owner),
                    'id' => $owner->id,
                ],
                'actions' => [
                    [
                        'handler' => AddBalance::class,
                        'subtotal' => [
                            'value' => '5.00',
                            'currency' => 'EUR',
                        ],
                        'taxPercentage' => 0,
                        'description' => 'Test add balance 1'
                    ],
                    [
                        'handler' => AddBalance::class,
                        'subtotal' => [
                            'value' => '5.00',
                            'currency' => 'EUR',
                        ],
                        'taxPercentage' => 0,
                        'description' => 'Test add balance 2'
                    ],
                ],
            ],
        ];
        $this->assertEquals($payload, $check_payload);



        $this->assertNotEmpty($customerId);
        $this->assertEquals(0, $owner->orderItems()->count());
        $this->assertEquals(0, $owner->orders()->count());
    }

    /** @test */
    public function createsMolliePayment()
    {
        $owner = factory(User::class)->create();
        $this->assertEquals(0, $owner->orderItems()->count());
        $this->assertEquals(0, $owner->orders()->count());

        $builder = new FirstPaymentBuilder($owner, [
            'description' => 'Test mandate payment',
            'redirectUrl' => 'https://www.example.com',
        ]);

        $builder->inOrderTo([
            new AddBalance(
                $owner,
                money(500, 'EUR'),
                'Test add balance 1'
            ),
            new AddBalance(
                $owner,
                money(500, 'EUR'),
                'Test add balance 2'
            ),
        ]);

        $payment = $builder->create();

        $this->assertEquals(0, $owner->orderItems()->count());
        $this->assertEquals(0, $owner->orders()->count());

        $this->assertInstanceOf(Payment::class, $payment);

        // For creating a new paid first payment, use:
        // dd(
        //     $payment->getCheckoutUrl(), // visit this Mollie checkout url and set status to 'paid'
        //     $payment->id // store this in phpunit.xml: MANDATE_PAYMENT_PAID_ID
        // );
    }
}
