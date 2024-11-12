<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/src/Routes/routes.php';

// Configure Flight views
Flight::set('flight.views.path', __DIR__ . '/views');

Flight::start();
