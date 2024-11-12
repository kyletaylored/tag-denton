<?php

namespace App\Controllers;

use App\Helpers\MongoHelper;

class RedirectController
{
    public static function handleRedirect($key)
    {
        $db = MongoHelper::getMongoConnection();
        $collection = $db->links;

        $entry = $collection->findOne(['key' => $key]);

        if (!$entry) {
            return '/404.html';
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $platform = (stripos($userAgent, 'android') !== false) ? 'android' : 'ios';

        // Determine the redirect URL
        $redirectUrl = $platform === 'android'
            ? $entry['data']['android_scheme'] ?? $entry['url']
            : $entry['data']['ios_scheme'] ?? $entry['url'];

        // Track the visit in Google Analytics
        $analytics = new AnalyticsController();
        $analytics->trackVisit($key);

        // Redirect the user
        return $redirectUrl;
    }
}
