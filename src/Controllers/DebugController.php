<?php

namespace App\Controllers;

use App\Controllers\AnalyticsController;

class DebugController
{
    public static function handleDebug()
    {
        return \App\Helpers\RequestHelper::getRequestDetails();
    }

    public static function handleServerEnvDebug()
    {
        // Return _SERVER and _ENV data
        return [
            '_SERVER' => $_SERVER,
            '_ENV' => $_ENV,
        ];
    }

    public static function handleAnalyticsDebug()
    {
        $analytics = new AnalyticsController();
        $payload = $analytics->createEventPayload('debug_event', ['debug' => 'test']);
        return $payload;
    }
}
