<?php

use Faker\Generator as Faker;
use Carbon\Carbon;
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
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});



$factory->define(App\Concert::class, function (Faker $faker) {
    return [
        'title' => $faker->title,
        'description' => $faker->paragraph,
        'datetime' => $faker->dateTime(),
        'price' => $faker->randomNumber(3),
        'venue' => $faker->word,
        'venue_address' => $faker->streetAddress,
        'city' => $faker->city,
        'state' => $faker->city,
        'zip' => $faker->postcode,
        'additional_info' => $faker->paragraph,
//        'published_at' => $faker->dateTime(),
    ];
});

$factory->state(App\Concert::class, 'published',function (Faker $faker) {
return [
    'published_at' => Carbon::parse('-1 day'),
    ];
});

$factory->state(App\Concert::class, 'unpublished',function (Faker $faker) {
return [
    'published_at' => null,
    ];
});

