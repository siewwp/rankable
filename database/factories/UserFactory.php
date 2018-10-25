<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'xpoint' => $faker->numberBetween(0, 1000),
        'ypoint' => $faker->numberBetween(0, 1000),
        'total_like' => $faker->numberBetween(0, 1000),
        'clan_id' => $faker->numberBetween(1, 10),
    ];
});
