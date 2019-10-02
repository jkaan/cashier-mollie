<?php

namespace Fitblocks\Cashier\Events;

use Fitblocks\Cashier\Subscription;

class SubscriptionQuantityUpdated
{
    /**
     * @var \Fitblocks\Cashier\Subscription
     */
    public $subscription;

    /**
     * @var int
     */
    public $oldQuantity;

    public function __construct(Subscription $subscription, int $oldQuantity)
    {
        $this->subscription = $subscription;
        $this->oldQuantity = $oldQuantity;
    }
}
