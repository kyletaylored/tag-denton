<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function before($params)
    {
        $session = \Flight::session();
        if (!$session->exist('is_logged_in')) {
            \Flight::redirect('/login');
            exit;
        }
    }
}
