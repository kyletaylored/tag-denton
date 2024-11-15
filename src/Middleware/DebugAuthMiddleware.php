<?php

namespace App\Middleware;

class DebugAuthMiddleware {
    public function before($params) {
        $authUser = $_SERVER['PHP_AUTH_USER'] ?? null;
        $authPass = $_SERVER['PHP_AUTH_PW'] ?? null;

        $allowedUsername = $_ENV['DEBUG_USERNAME'] ?? 'admin';
        $allowedPassword = $_ENV['DEBUG_PASSWORD'] ?? 'password';

        if ($authUser !== $allowedUsername || $authPass !== $allowedPassword) {
            header('WWW-Authenticate: Basic realm="Debug Access"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Unauthorized';
            exit;
        }
    }
}
