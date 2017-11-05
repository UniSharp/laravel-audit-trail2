<?php

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

// setup database
$database = new Illuminate\Database\Capsule\Manager;
$database->addConnection([
    'driver'   => 'sqlite',
    'database' => ':memory:',
]);
$database->setEventDispatcher(
    new Illuminate\Events\Dispatcher(new Illuminate\Container\Container)
);
$database->bootEloquent();
$database->setAsGlobal();
