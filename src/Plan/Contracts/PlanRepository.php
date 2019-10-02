<?php

declare(strict_types=1);

namespace Fitblocks\Cashier\Plan\Contracts;

interface PlanRepository
{
    /**
     * @param string $name
     * @return null|\Fitblocks\Cashier\Plan\Contracts\Plan
     */
    public static function find(string $name);

    /**
     * @param string $name
     * @return \Fitblocks\Cashier\Plan\Contracts\Plan
     */
    public static function findOrFail(string $name);
}
