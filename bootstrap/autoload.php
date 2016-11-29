<?php

require __DIR__.'/../vendor/autoload.php';

define('LOGS_DIR', __DIR__ . '/../logs/');

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
try {
    $dotenv->load();
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
    die;
}

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('db_host'),
    'database'  => getenv('db_name'),
    'username'  => getenv('db_user'),
    'password'  => getenv('db_pass'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();