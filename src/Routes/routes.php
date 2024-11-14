<?php

use App\Controllers\ProxyController;
use App\Controllers\RedirectController;
use App\Controllers\LinksController;
use Ghostff\Session\Session;

Flight::register('session', Session::class);

// Home Page
Flight::route('GET /', function () {
    $content = Flight::view()->fetch('home');
    Flight::render('layouts/default', [
        'title' => 'Welcome to Tag Denton',
        'description' => 'Discover and explore Denton landmarks easily.',
        'content' => $content,
    ]);
});

// Login
Flight::route('GET /login', function () {
    $content = Flight::view()->fetch('login');
    Flight::render('layouts/default', [
        'title' => 'Login - Tag Denton',
        'description' => 'Login to manage your Tag Denton links.',
        'content' => $content,
    ]);
});

Flight::route('POST /login', function () {
    $username = Flight::request()->data->username;
    $password = Flight::request()->data->password;

    if (App\Controllers\AuthController::login($username, $password)) {
        $session = Flight::session();
        $session->set('is_logged_in', true);
        $session->set('username', $username);
        $session->commit();
        Flight::redirect('/dashboard');
    } else {
        Flight::halt(401, 'Invalid credentials');
    }
});

// Logout
Flight::route('GET /logout', function () {
    $session = Flight::session();
    $session->destroy();
    Flight::redirect('/login');
});

// Dashboard
Flight::route('GET /dashboard', function () {
    $session = Flight::session();
    if (!$session->exist('is_logged_in')) {
        Flight::redirect('/login');
    } else {
        $content = Flight::view()->fetch('dashboard', ['username' => $session->get('username')]);
        Flight::render('layouts/default', [
            'title' => 'Dashboard - Tag Denton',
            'description' => 'Manage your Tag Denton links.',
            'content' => $content,
        ]);
    }
});

// Proxy
Flight::route('POST /proxy', function () {
    $session = Flight::session();
    if (!$session->get('is_logged_in')) {
        Flight::halt(403, 'Unauthorized');
    }

    $response = ProxyController::handleProxyRequest(Flight::request()->data->getData());
    Flight::json($response);
});

// Redirect
Flight::route('GET /redirect/@key', function ($key) {
    $redirectUrl = RedirectController::handleRedirect($key);

    if ($redirectUrl === false) {
        Flight::render('404', [
            'title' => 'Page Not Found',
            'description' => 'The page you are looking for does not exist.'
        ]);
        Flight::halt(404); // Send HTTP 404 status
    } else {
        Flight::response()->header('Cache-Control', 'max-age=15778476, public'); // 6 months
        Flight::redirect($redirectUrl);
    }
});

// Admin Links
Flight::route('GET /admin/links', function () {
    $session = Flight::session();
    if (!$session->get('is_logged_in')) {
        Flight::halt(403, 'Unauthorized');
    }

    $links = LinksController::getAllLinks();
    Flight::json($links);
});

// 404 Handler
Flight::map('notFound', function () {
    error_log('404 Not Found: ' . Flight::request()->url);
    Flight::render('404', [
        'title' => 'Page Not Found',
        'description' => 'The page you are looking for does not exist.',
        'content' => '<h1>404 - Page Not Found</h1>'
    ]);
    Flight::halt(404);
});
