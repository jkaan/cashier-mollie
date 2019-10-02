<?php

namespace Fitblocks\Cashier\Events;

use Fitblocks\Cashier\Credit\Credit;

class BalanceTurnedStale
{
    /**
     * @var \Fitblocks\Cashier\Credit\Credit
     */
    public $credit;

    public function __construct(Credit $credit)
    {
        $this->credit = $credit;
    }
}
