<?php

namespace App\Controllers;

use App\Helpers\RequestHelper;
use App\Helpers\MongoHelper;
use App\Controllers\AnalyticsController;

class RedirectController
{
    public static function handleRedirect($key)
    {
        $db = MongoHelper::getMongoConnection();
        $collection = $db->links;

        $entry = $collection->findOne(['key' => $key]);

        if (!$entry) {
            return false; // Return false for not found
        }

        // Extract Request Info
        $requestInfo = RequestHelper::getRequestDetails();
        $deviceData = $requestInfo['device_data'];
        $geoData = $requestInfo['geo_data'];

        // Determine the redirect URL based on the platform
        $osFamily = strtolower($deviceData['os']['family'] ?? 'unknown');
        $redirectUrl = match ($osFamily) {
            'android' => $entry['data']['android_scheme'] ?? $entry['url'],
            'ios' => $entry['data']['ios_scheme'] ?? $entry['url'],
            default => $entry['url'],
        };

        // Single analytics call
        $analytics = new AnalyticsController();
        $analytics->trackRedirectEvent(
            $key,
            $requestInfo['device_data'],
            $requestInfo['geo_data'],
            $redirectUrl,
            $entry['url']
        );

        return $redirectUrl;
    }

    public static function getKeyData($key)
    {
        // Connect to MongoDB
        $db = MongoHelper::getMongoConnection();
        $collection = $db->links;

        // Fetch the entry for the given key
        $entry = $collection->findOne(['key' => $key]);

        // Return the entry as an array or null if not found
        return $entry ? iterator_to_array($entry) : null;
    }
}
