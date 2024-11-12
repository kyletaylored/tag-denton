<?php

require 'vendor/autoload.php';

// Allowed hostnames for production
$allowedHosts = ['tagdenton.com', 'www.tagdenton.com', 'localhost'];

// Get the `Host` header from the request
$requestHost = $_SERVER['HTTP_HOST'] ?? '';

// Check if the current environment is local or production
$isLocal = in_array($requestHost, ['localhost', '127.0.0.1']);

// Redirect only in production
if (!$isLocal && !in_array($requestHost, $allowedHosts)) {
    // Redirect to the primary domain with the same path and query string
    $redirectUrl = 'https://tagdenton.com' . $_SERVER['REQUEST_URI'];
    header("Location: $redirectUrl", true, 301);
    exit();
}

// Conditional .env loading for local development
if ($isLocal && file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Start the application
require_once __DIR__ . '/src/Routes/routes.php';
Flight::set('flight.views.path', __DIR__ . '/views');
Flight::start();
