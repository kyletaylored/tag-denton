<?php

namespace App\Controllers;

use App\Helpers\RequestHelper;
use App\Helpers\MongoHelper;

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

        // Track the visit in Google Analytics
        $analytics = new AnalyticsController();
        $analytics->trackCustomEvent('app_redirect', array_merge(
            [
                'event_category' => 'Redirect',
                'event_action' => 'click',
                'redirect_key' => $key,
                'platform' => $osFamily,
                'target_url' => $redirectUrl,
                'original_url' => $entry['url'],
            ],
            $deviceData,
            $geoData
        ));

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
