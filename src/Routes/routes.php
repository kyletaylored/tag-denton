<?php

use App\Controllers\ProxyController;
use App\Controllers\RedirectController;

Flight::route('POST /proxy', function () {
    if (!App\Controllers\AuthController::isAuthenticated()) {
        Flight::halt(403, 'Unauthorized');
    }

    $response = App\Controllers\ProxyController::handleProxyRequest(Flight::request()->data->getData());
    Flight::json($response);
});


Flight::route('GET /redirect/@key', function ($key) {
    $redirectUrl = RedirectController::handleRedirect($key);
    if ($redirectUrl === '/404.html') {
        Flight::redirect('/404.html');
    } else {
        Flight::redirect($redirectUrl);
    }
});

Flight::route('GET /', function() {
    if (!App\Controllers\AuthController::isAuthenticated()) {
        Flight::redirect('/login');
    } else {
        Flight::render('home');
    }
});

Flight::route('GET /login', function () {
    Flight::render('login');
});

Flight::route('POST /login', function () {
    $username = Flight::request()->data->username;
    $password = Flight::request()->data->password;

    if (App\Controllers\AuthController::login($username, $password)) {
        Flight::redirect('/');
    } else {
        Flight::halt(401, 'Invalid credentials');
    }
});

Flight::route('GET /logout', function () {
    App\Controllers\AuthController::logout();
    Flight::redirect('/login');
});

Flight::route('GET /admin/links', function () {
    if (!App\Controllers\AuthController::isAuthenticated()) {
        Flight::halt(403, 'Unauthorized');
    }

    $links = App\Controllers\LinksController::getAllLinks();
    Flight::json($links);
});
