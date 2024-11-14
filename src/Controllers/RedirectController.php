<?php

namespace App\Controllers;

use GeoIp2\Database\Reader;
use WhichBrowser\Parser;
use App\Helpers\MongoHelper;

class RedirectController
{
    const MAXMIND_DB_PATH = __DIR__ . '/../../geolite2-city/GeoLite2-City.mmdb';

    private static function parseUserAgent($userAgent) {
        $parser = new Parser($userAgent);
        
        $data = [
            'browser' => $parser->browser->name ?? 'Unknown',
            'browser_version' => $parser->browser->version->value ?? 'Unknown',
            'os' => $parser->os->name ?? 'Unknown',
            'os_version' => $parser->os->version->value ?? 'Unknown',
            'device_type' => $parser->getType(),
            'device_brand' => $parser->device->brand ?? 'Unknown',
            'device_model' => $parser->device->model ?? 'Unknown'
        ];

        return $data;
    }

    private static function getGeoData($ip) {
        try {
            // Update path to where you store the MaxMind database
            $reader = new Reader(self::MAXMIND_DB_PATH);
            $record = $reader->city($ip);
            
            return [
                'country' => $record->country->name ?? 'Unknown',
                'country_iso' => $record->country->isoCode ?? 'Unknown',
                'region' => $record->mostSpecificSubdivision->name ?? 'Unknown',
                'region_iso' => $record->mostSpecificSubdivision->isoCode ?? 'Unknown',
                'city' => $record->city->name ?? 'Unknown',
                'postal' => $record->postal->code ?? 'Unknown',
                'latitude' => $record->location->latitude ?? 0,
                'longitude' => $record->location->longitude ?? 0,
                'timezone' => $record->location->timeZone ?? 'Unknown',
                'continent' => $record->continent->name ?? 'Unknown'
            ];
        } catch (\Exception $e) {
            error_log("GeoIP lookup failed: " . $e->getMessage());
            return [
                'country' => 'Unknown',
                'country_iso' => 'Unknown',
                'region' => 'Unknown',
                'region_iso' => 'Unknown',
                'city' => 'Unknown',
                'postal' => 'Unknown',
                'latitude' => 0,
                'longitude' => 0,
                'timezone' => 'Unknown',
                'continent' => 'Unknown'
            ];
        }
    }

    private static function getReferrerInfo() {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        if (empty($referrer)) return null;

        $parsedUrl = parse_url($referrer);
        return [
            'source' => $parsedUrl['host'] ?? 'direct',
            'full_url' => $referrer
        ];
    }

    public static function handleRedirect($key)
    {
        $db = MongoHelper::getMongoConnection();
        $collection = $db->links;

        $entry = $collection->findOne(['key' => $key]);

        if (!$entry) {
            return '/404.html';
        }

        // Get client IP, handling proxy cases
        $ip = $_SERVER['HTTP_CLIENT_IP'] 
            ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? '0.0.0.0';

        // If multiple IPs (X-Forwarded-For can contain multiple), get the first one
        $ip = trim(explode(',', $ip)[0]);

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $platform = (stripos($userAgent, 'android') !== false) ? 'android' : 'ios';

        // Get device and browser info
        $deviceInfo = self::parseUserAgent($userAgent);
        
        // Get geo information
        $geoData = self::getGeoData($ip);
        
        // Get referrer information
        $referrerInfo = self::getReferrerInfo();

        // Determine the redirect URL
        $redirectUrl = $platform === 'android'
            ? $entry['data']['android_scheme'] ?? $entry['url']
            : $entry['data']['ios_scheme'] ?? $entry['url'];

        // Track the visit in Google Analytics
        $analytics = new AnalyticsController();
        $analytics->trackCustomEvent('app_redirect', [
            'event_category' => 'Redirect',
            'event_action' => 'click',
            'redirect_key' => $key,
            'platform' => $platform,
            'target_url' => $redirectUrl,
            'original_url' => $entry['url'],
            
            // Device and browser info
            'device_category' => $deviceInfo['device_type'],
            'device_brand' => $deviceInfo['device_brand'],
            'device_model' => $deviceInfo['device_model'],
            'browser' => $deviceInfo['browser'],
            'browser_version' => $deviceInfo['browser_version'],
            'operating_system' => $deviceInfo['os'],
            'os_version' => $deviceInfo['os_version'],
            
            // Location info
            'country' => $geoData['country'],
            'country_iso' => $geoData['country_iso'],
            'region' => $geoData['region'],
            'region_iso' => $geoData['region_iso'],
            'city' => $geoData['city'],
            'postal_code' => $geoData['postal'],
            'latitude' => $geoData['latitude'],
            'longitude' => $geoData['longitude'],
            'timezone' => $geoData['timezone'],
            'continent' => $geoData['continent'],
            
            // Session and traffic source
            'referrer_source' => $referrerInfo ? $referrerInfo['source'] : 'direct',
            'referrer_url' => $referrerInfo ? $referrerInfo['full_url'] : '',
            
            // Additional parameters
            'language' => substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown', 0, 2),
            'timestamp' => time()
        ]);

        return $redirectUrl;
    }
}