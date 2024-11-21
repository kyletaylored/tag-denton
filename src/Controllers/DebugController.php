<?php

namespace App\Controllers;

use App\Helpers\RequestHelper;
use App\Helpers\EnvConfig;

class DebugController
{
    public static function handleDebug()
    {
        return RequestHelper::getRequestDetails();
    }

    public static function handleServerEnvDebug()
    {
        return [
            '_SERVER' => $_SERVER,
            '_ENV' => $_ENV,
        ];
    }

    public static function handleAnalyticsDebug($key = 'debug_key')
    {
        // Get request details
        $requestInfo = RequestHelper::getRequestDetails();
        $deviceData = $requestInfo['device_data'];
        $geoData = $requestInfo['geo_data'];

        // Initialize analytics controllers
        $analytics = new AnalyticsController();
        return $analytics->getDebugInfo();
    }
}