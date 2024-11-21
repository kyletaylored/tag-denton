<?php

use App\Controllers\DebugController;
use App\Controllers\ProxyController;
use App\Controllers\RedirectController;
use App\Controllers\LinksController;
use App\Middleware\DebugAuthMiddleware;
use App\Middleware\AuthMiddleware;
use Ghostff\Session\Session;
use Kint\Kint;

Flight::register('session', Session::class);

// Public routes
Flight::route('GET /', function () {
    $content = Flight::view()->fetch('home');
    Flight::render('layouts/default', [
        'title' => 'Welcome to Tag Denton',
        'description' => 'Discover and explore Denton landmarks easily.',
        'content' => $content,
    ]);
});

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

Flight::route('GET /logout', function () {
    $session = Flight::session();
    $session->destroy();
    Flight::redirect('/login');
});

// Group routes requiring authentication
Flight::group('', function () {
    // Dashboard
    Flight::route('GET /dashboard', function () {
        $session = Flight::session();
        $content = Flight::view()->fetch('dashboard', ['username' => $session->get('username')]);
        Flight::render('layouts/default', [
            'title' => 'Dashboard - Tag Denton',
            'description' => 'Manage your Tag Denton links.',
            'content' => $content,
        ]);
    });

    // Proxy
    Flight::route('POST /proxy', function () {
        $response = ProxyController::handleProxyRequest(Flight::request()->data->getData());
        Flight::json($response);
    });

    // Admin Links
    Flight::route('GET /admin/links', function () {
        $links = LinksController::getAllLinks();
        Flight::json($links);
    });
}, [new AuthMiddleware()]);

// Public route for redirects
Flight::route('GET /redirect/@key', function ($key) {
    $redirectUrl = RedirectController::handleRedirect($key);
    if ($redirectUrl === false) {
        Flight::render('404');
        Flight::halt(404);
    } else {
        Flight::redirect($redirectUrl);
    }
});

// Debug routes (protected with Basic Auth)
Flight::group('/debug', function () {
    Flight::route('/request', function () {
        $data = \App\Helpers\RequestHelper::getRequestDetails();
        if (Flight::request()->query->format === 'json') {
            Flight::json($data);
        } else {
            Kint::dump($data);
        }
    });

    Flight::route('/server', function () {
        $data = DebugController::handleServerEnvDebug();
        if (Flight::request()->query->format === 'json') {
            Flight::json($data);
        } else {
            Kint::dump($data);
        }
    });

    Flight::route('/analytics', function () {
        $data = DebugController::handleAnalyticsDebug();
        if (Flight::request()->query->format === 'json') {
            Flight::json($data);
        } else {
            Kint::dump($data);
        }
    });
}, [new DebugAuthMiddleware()]);

// 404 Handler
Flight::map('notFound', function () {
    error_log('404 Not Found: ' . Flight::request()->url);
    $content = Flight::view()->fetch('404');
    Flight::render('layouts/default', [
        'title' => '404 - Page Not Found',
        'description' => 'Discover and explore Denton landmarks easily.',
        'content' => $content,
    ]);
    Flight::halt(404);
});
