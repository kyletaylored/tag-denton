<?php

namespace App\Controllers;

use App\Helpers\MongoHelper;

class ProxyController
{
    public static function handleProxyRequest($request)
    {
        $db = MongoHelper::getMongoConnection();
        $collection = $db->selectCollection('links');

        $url = $request['url'];

        // Check if the URL already exists
        $existingEntry = $collection->findOne(['url' => $url]);
        if ($existingEntry) {
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $url]));
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        // Determine redirect type
        $hasDeepLink = isset($data['ios_scheme']) || isset($data['android_scheme']);
        $key = bin2hex(random_bytes(5));

        // Store in MongoDB
        $collection->insertOne([
            'url' => $url,
            'key' => $key,
            'data' => $data,
            'has_deep_link' => $hasDeepLink
        ]);

        return ['key' => $key];
    }
}
