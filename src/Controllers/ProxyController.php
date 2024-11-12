<?php

namespace App\Controllers;

use App\Helpers\MongoHelper;

class ProxyController
{
    public static function handleProxyRequest($request)
    {
        $db = MongoHelper::getMongoConnection();
        $url = $request['url'];

        // Sanitize and normalize the URL
        $sanitizedUrl = self::sanitizeUrl($url);

        if (!$sanitizedUrl) {
            return ['error' => 'Invalid URL'];
        }

        $collection = $db->links;
        $existingEntry = $collection->findOne(['url' => $sanitizedUrl]);

        if ($existingEntry) {
            // Return the existing key if already cached
            return ['key' => $existingEntry['key']];
        }

        // Call the external API
        $apiUrl = 'https://app.urlgeni.us/api/internal/test_url';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $sanitizedUrl]));
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        // Determine redirect type
        $hasDeepLink = isset($data['ios_scheme']) || isset($data['android_scheme']);
        $key = bin2hex(random_bytes(5));

        // Store in MongoDB
        $collection->insertOne([
            'url' => $sanitizedUrl,
            'key' => $key,
            'data' => $data,
            'has_deep_link' => $hasDeepLink
        ]);

        // Return the unique key
        return ['key' => $key];
    }

    private static function sanitizeUrl($url)
    {
        // Parse the URL
        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['host']) || !isset($parsedUrl['path'])) {
            return null; // Invalid URL
        }

        // Reconstruct the sanitized URL
        $sanitizedPath = rtrim($parsedUrl['path'], '/');
        return "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$sanitizedPath}";
    }
}
