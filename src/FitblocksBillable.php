<?php

namespace Fitblocks\Cashier;

use Fitblocks\Cashier\Credit\Credit;
use Fitblocks\Cashier\Events\MandateClearedFromBillable;
use Fitblocks\Cashier\Order\Order;
use Fitblocks\Cashier\Order\OrderItem;
use Fitblocks\Cashier\Plan\Contracts\PlanRepository;
use Fitblocks\Cashier\SubscriptionBuilder\FirstPaymentSubscriptionBuilder;
use Fitblocks\Cashier\SubscriptionBuilder\MandatedSubscriptionBuilder;
use Fitblocks\Cashier\Traits\PopulatesMollieCustomerFields;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Types\MandateMethod;
use Money\Money;

trait FitblocksBillable
{
    /**
     * Get all of the subscriptions for the billable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptionsFitblocks()
    {
        return $this->morphMany(Subscription::class, 'owner');
    }
}
