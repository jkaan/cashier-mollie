<?php

namespace Fitblocks\Cashier\Database\Factories;

use Faker\Generator as Faker;
use Fitblocks\Cashier\Tests\Fixtures\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'tax_percentage' => 0,
    ];
});
