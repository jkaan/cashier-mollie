<?php

namespace Fitblocks\Cashier\Tests\Order;

use Fitblocks\Cashier\Order\Invoice;
use Fitblocks\Cashier\Order\Order;
use Fitblocks\Cashier\Tests\BaseTestCase;
use Fitblocks\Cashier\Tests\Fixtures\User;

class OrderCollectionTest extends BaseTestCase
{
    /** @test */
    public function canGetInvoices()
    {
        $this->withPackageMigrations();
        $user = factory(User::class)->create();
        $orders = $user->orders()->saveMany(factory(Order::class, 2)->make());

        $invoices = $orders->invoices();

        $this->assertCount(2, $invoices);
        $this->assertInstanceOf(Invoice::class, $invoices->first());
    }
}
