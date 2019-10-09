<?php

namespace Fitblocks\Cashier;

use Fitblocks\Cashier\Order\Order;

trait FitblocksBillable
{
    public function subscriptionsFitblocks()
    {
        return $this->morphMany(Subscription::class, 'owner');
    }

    public function ordersFitblocks()
    {
        return $this->morphMany(Order::class, 'owner');
    }
}
