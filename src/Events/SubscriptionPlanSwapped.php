<?php

namespace Fitblocks\Cashier\Events;

use Fitblocks\Cashier\Subscription;

class SubscriptionPlanSwapped
{
    /**
     * @var \Fitblocks\Cashier\Subscription
     */
    public $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
