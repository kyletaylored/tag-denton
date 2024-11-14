<?php

namespace App\Controllers;

use GeoIp2\Database\Reader;
use DeviceDetector\DeviceDetector;
use App\Helpers\MongoHelper;

class RedirectController
{
    const MAXMIND_DB_PATH = __DIR__ . '/../../geolite2-city/GeoLite2-City.mmdb';

    private static function parseUserAgent($userAgent) {

        $dd = new DeviceDetector($userAgent);
        $dd->parse();

        // Return bot info if detected
        if ($dd->isBot()) {
            return [
                'is_bot' => true,
                'bot_info' => $dd->getBot()
            ];
        }

        // Return parsed client and device info
        return [
            'is_bot' => false,
            'client' => $dd->getClient(), // Browser, app, etc.
            'os' => $dd->getOs(),
            'device' => [
                'type' => $dd->getDeviceName(), // e.g., Smartphone
                'brand' => $dd->getBrandName(), // e.g., Apple
                'model' => $dd->getModel(),     // e.g., iPhone X
                'is_smartphone' => $dd->isSmartphone(),
                'is_tablet' => $dd->isTablet()
            ]
        ];
    }

    private static function getGeoData($ip) {
        try {
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
            return false; // Return false for not found
        }

        // Get client IP, handling proxy cases
        $ip = $_SERVER['HTTP_CLIENT_IP'] 
            ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? '0.0.0.0';

        $ip = trim(explode(',', $ip)[0]);

        // Parse User Agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $deviceInfo = self::parseUserAgent($userAgent);

        // Get the platform family from DeviceDetector
        $osFamily = strtolower($deviceInfo['os_family'] ?? 'unknown');

        // Determine the redirect URL based on the platform
        $redirectUrl = match ($osFamily) {
            'android' => $entry['data']['android_scheme'] ?? $entry['url'],
            'ios' => $entry['data']['ios_scheme'] ?? $entry['url'],
            default => $entry['url'], // Fallback for other platforms
        };

        // Get geo and referrer info
        $geoData = self::getGeoData($ip);
        $referrerInfo = self::getReferrerInfo();

        // Track the visit in Google Analytics
        $analytics = new AnalyticsController();
        $analytics->trackCustomEvent('app_redirect', [
            'event_category' => 'Redirect',
            'event_action' => 'click',
            'redirect_key' => $key,
            'platform' => strtolower($deviceInfo['os_family'] ?? 'unknown'), // Use osFamily directly
            'target_url' => $redirectUrl,
            'original_url' => $entry['url'],
        
            // Device info
            'device_type' => $deviceInfo['device_name'] ?? 'Unknown',
            'device_brand' => $deviceInfo['device_brand'] ?? 'Unknown',
            'device_model' => $deviceInfo['device_model'] ?? 'Unknown',
            'browser' => $deviceInfo['client_info']['name'] ?? 'Unknown',
            'browser_version' => $deviceInfo['client_info']['version'] ?? 'Unknown',
            'os' => $deviceInfo['os_info']['name'] ?? 'Unknown',
            'os_version' => $deviceInfo['os_info']['version'] ?? 'Unknown',
        
            // Geo info
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
        
            // Additional parameters
            'language' => substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown', 0, 2),
            'timestamp' => time()
        ]);        

        return $redirectUrl;
    }
}
