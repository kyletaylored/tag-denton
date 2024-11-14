<?php

use App\Controllers\ProxyController;
use App\Controllers\RedirectController;
use App\Controllers\LinksController;
use Ghostff\Session\Session;

Flight::register('session', Session::class);

Flight::route('GET /login', function () {
    error_log('Accessing /login route');
    Flight::render('login');
});

Flight::route('POST /login', function () {
    error_log('Accessing /login (POST) route');
    $username = Flight::request()->data->username;
    $password = Flight::request()->data->password;

    if (App\Controllers\AuthController::login($username, $password)) {
        $session = Flight::session();
        $session->set('is_logged_in', true);
        $session->set('username', $username);
        $session->commit();
        Flight::redirect('/');
    } else {
        Flight::halt(401, 'Invalid credentials');
    }
});

Flight::route('GET /', function() {
    error_log('Accessing / route');
    $session = Flight::session();
    if (!$session->exist('is_logged_in')) {
        error_log('User is not authenticated, redirecting to /login');
        Flight::redirect('/login');
    } else {
        Flight::render('home', ['username' => $session->get('username')]);
    }
});

Flight::route('GET /logout', function () {
    $session = Flight::session();
    $session->destroy();
    Flight::redirect('/login');
});

Flight::route('POST /proxy', function () {
    $session = Flight::session();
    if (!$session->get('is_logged_in')) {
        Flight::halt(403, 'Unauthorized');
    }

    $response = ProxyController::handleProxyRequest(Flight::request()->data->getData());
    Flight::json($response);
});

Flight::route('GET /redirect/@key', function ($key) {
    $redirectUrl = RedirectController::handleRedirect($key);
    if ($redirectUrl === '/404.html') {
        Flight::redirect('/404.html');
    } else {
        Flight::response()->header('Cache-Control', 'max-age=15778476, public'); // 6 months
        Flight::redirect($redirectUrl);
    }
});

Flight::route('GET /admin/links', function () {
    $session = Flight::session();
    if (!$session->get('is_logged_in')) {
        Flight::halt(403, 'Unauthorized');
    }

    $links = LinksController::getAllLinks();
    Flight::json($links);
});

Flight::map('notFound', function() {
    error_log('404 Not Found: ' . Flight::request()->url);
    Flight::render('404');
    Flight::halt(404);
});
