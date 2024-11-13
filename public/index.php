<?php

// Define the project root directory
define('PROJECT_ROOT', dirname(__DIR__ . '../'));

// Autoload dependencies
require_once PROJECT_ROOT . '/vendor/autoload.php';

// Allowed hostnames for production
$allowedHosts = ['tagdenton.com', 'www.tagdenton.com'];

// Get the `Host` header from the request
$requestHost = $_SERVER['HTTP_HOST'] ?? '';

// Check if the current environment is local or production
$isLocal = preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $requestHost);

// Redirect only in production
if (!$isLocal && !in_array($requestHost, $allowedHosts)) {
    // Redirect to the primary domain with the same path and query string
    $redirectUrl = 'https://tagdenton.com' . $_SERVER['REQUEST_URI'];
    header("Location: $redirectUrl", true, 301);
    exit();
}

// Conditional .env loading for local development
if ($isLocal && file_exists(PROJECT_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT);
    $dotenv->load();
}

// Start the application
require_once PROJECT_ROOT . '/src/Routes/routes.php';
Flight::set('flight.views.path', PROJECT_ROOT . '/views');
Flight::start();
