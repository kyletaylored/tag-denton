<?php

namespace App\Middleware;

use App\Helpers\EnvConfig;

class DebugAuthMiddleware {
    public function before($params) {
        $authUser = $_SERVER['PHP_AUTH_USER'] ?? null;
        $authPass = $_SERVER['PHP_AUTH_PW'] ?? null;

        $allowedUsername = EnvConfig::get('DEBUG_USERNAME');
        $allowedPassword = EnvConfig::get('DEBUG_PASSWORD');

        if ($authUser !== $allowedUsername || $authPass !== $allowedPassword) {
            header('WWW-Authenticate: Basic realm="Debug Access"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Unauthorized';
            exit;
        }
    }
}
