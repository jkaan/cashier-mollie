<?php

namespace Fitblocks\Cashier;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Fitblocks\Cashier\Order\OrderInvoiceSubscriber;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        OrderInvoiceSubscriber::class,
    ];
}
