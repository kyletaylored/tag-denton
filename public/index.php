<?php

// Define the project root directory
define('PROJECT_ROOT', dirname(__DIR__));

// Autoload dependencies
require_once PROJECT_ROOT . '/vendor/autoload.php';

// Allowed hostnames for production
$allowedHosts = ['tagdenton.com', 'www.tagdenton.com'];

// Determine the request hostname
$requestHost = $_SERVER['HTTP_HOST'] ?? '';

// Check if the current environment is local
$isLocal = preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $requestHost);

// Redirect to the primary domain if not local and the hostname is invalid
if (!$isLocal && !in_array($requestHost, $allowedHosts)) {
    $redirectUrl = 'https://tagdenton.com' . $_SERVER['REQUEST_URI'];
    header('Location: ' . $redirectUrl, true, 301);
    exit;
}

// Load environment variables in local development
if ($isLocal && file_exists(PROJECT_ROOT . '/.env')) {
    Dotenv\Dotenv::createImmutable(PROJECT_ROOT)->load();
}

// Configure FlightPHP
Flight::set('flight.views.path', PROJECT_ROOT . '/views');

// Load application routes
require_once PROJECT_ROOT . '/src/Routes/routes.php';

// Start the application
Flight::start();
