<?php

use App\Controllers\ProxyController;
use App\Controllers\RedirectController;

Flight::route('POST /proxy', function () {
    $response = ProxyController::handleProxyRequest(Flight::request()->data->getData());
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
    Flight::render('home');
});

