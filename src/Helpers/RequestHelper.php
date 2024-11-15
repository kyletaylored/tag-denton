<?php

namespace App\Helpers;

use DeviceDetector\DeviceDetector;
use GeoIp2\Database\Reader;

class RequestHelper
{
    const MAXMIND_DB_PATH = __DIR__ . '/../../geolite2-city/GeoLite2-City.mmdb';

    public static function getRequestDetails()
    {
        $ip = self::getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $deviceData = self::parseUserAgent($userAgent);
        $geoData = self::getGeoData($ip);

        return [
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_data' => $deviceData,
            'geo_data' => $geoData,
        ];
    }

    private static function getClientIp()
    {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';

        // If multiple IPs are present in a forwarded header, use the first
        if (strpos($ip, ',') !== false) {
            $ip = explode(',', $ip)[0];
        }

        return trim($ip);
    }

    private static function parseUserAgent($userAgent)
    {
        $dd = new DeviceDetector($userAgent);
        $dd->parse();

        if ($dd->isBot()) {
            return [
                'is_bot' => true,
                'bot_info' => $dd->getBot(),
            ];
        }

        return [
            'is_bot' => false,
            'client' => $dd->getClient(),
            'os' => $dd->getOs(),
            'device' => [
                'type' => $dd->getDeviceName(),
                'brand' => $dd->getBrandName(),
                'model' => $dd->getModel(),
                'is_smartphone' => $dd->isSmartphone(),
                'is_tablet' => $dd->isTablet(),
            ],
        ];
    }

    private static function getGeoData($ip)
    {
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
                'continent' => $record->continent->name ?? 'Unknown',
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
                'continent' => 'Unknown',
            ];
        }
    }
}
