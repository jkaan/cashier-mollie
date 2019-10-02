<?php

namespace Fitblocks\Cashier\Database\Factories;

use Faker\Generator as Faker;
use Fitblocks\Cashier\Order\OrderItem;
use Fitblocks\Cashier\Subscription;
use Fitblocks\Cashier\Tests\Fixtures\User;

$factory->define(OrderItem::class, function (Faker $faker) {
    return [
        'owner_type' => User::class,
        'owner_id' => 1,
        'orderable_type' => Subscription::class,
        'orderable_id' => 1,
        'description' => 'Some dummy description',
        'unit_price' => 12150,
        'quantity' => 1,
        'tax_percentage' => 21.5,
        'currency' => 'EUR',
        'process_at' => now()->subMinute(),
    ];
});

$factory->state(OrderItem::class, 'unlinked', [
    'orderable_type' => null,
    'orderable_id' => null,
]);

$factory->state(OrderItem::class, 'unprocessed', [
    'order_id' => null,
]);

$factory->state(OrderItem::class, 'processed', [
    'order_id' => 1,
]);

$factory->state(OrderItem::class, 'EUR', [
    'currency' => 'EUR',
]);

$factory->state(OrderItem::class, 'USD', [
    'currency' => 'USD',
]);
