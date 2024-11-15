<?php

namespace App\Controllers;

use App\Helpers\EnvConfig;

class DebugController
{
    public static function handleDebug()
    {
        return \App\Helpers\RequestHelper::getRequestDetails();
    }

    public static function handleServerEnvDebug($username, $password)
    {
        // Define allowed credentials (you can also use environment variables)
        $allowedUsername = EnvConfig::get('DEBUG_USERNAME');
        $allowedPassword = EnvConfig::get('DEBUG_PASSWORD');

        // Validate Basic Auth credentials
        if ($username !== $allowedUsername || $password !== $allowedPassword) {
            header('WWW-Authenticate: Basic realm="Debug Access"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Unauthorized';
            exit;
        }

        // Return _SERVER and _ENV data
        return [
            '_SERVER' => $_SERVER,
            '_ENV' => $_ENV,
        ];
    }
}
