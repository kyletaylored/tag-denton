<?php

namespace App\Controllers;

class AnalyticsController
{
    private string $measurementId;
    private string $apiSecret;
    private string $endpoint = 'https://www.google-analytics.com/mp/collect';

    public function __construct()
    {
        $this->measurementId = getenv('GA_MEASUREMENT_ID');
        $this->apiSecret = getenv('GA_API_SECRET');
        
        if (!$this->measurementId || !$this->apiSecret) {
            throw new \RuntimeException('GA4 credentials not configured');
        }
    }

    /**
     * Generate a unique client ID based on IP, user-agent, and timestamp.
     */
    private function generateClientId(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown_ip';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown_agent';
        $timestamp = microtime(true);

        return hash('sha256', $ip . $userAgent . $timestamp);
    }

    /**
     * Track a custom event using the GA4 Measurement Protocol
     */
    public function trackCustomEvent(
        string $eventName,
        array $params = [],
        ?string $clientId = null
    ): bool {
        $clientId = $clientId ?? $this->generateClientId();
        
        $payload = [
            'client_id' => $clientId,
            'events' => [
                [
                    'name' => $eventName,
                    'params' => $params
                ]
            ]
        ];

        $url = sprintf(
            '%s?measurement_id=%s&api_secret=%s',
            $this->endpoint,
            $this->measurementId,
            $this->apiSecret
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: PHP GA4 Client/1.0'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 204) {
            error_log(sprintf(
                'GA4 tracking failed: HTTP %d, Response: %s',
                $httpCode,
                $response
            ));
            return false;
        }

        return true;
    }

    /**
     * Track a page view event
     */
    public function trackPageView(string $pageTitle, string $pagePath): bool
    {
        return $this->trackCustomEvent('page_view', [
            'page_title' => $pageTitle,
            'page_location' => $pagePath
        ]);
    }

    /**
     * Track an item selection event
     */
    public function trackItemSelection(string $itemId, string $itemName): bool
    {
        return $this->trackCustomEvent('select_item', [
            'items' => [[
                'item_id' => $itemId,
                'item_name' => $itemName
            ]]
        ]);
    }

    /**
     * Track a conversion event
     */
    public function trackConversion(string $conversionId, float $value = 0.0): bool
    {
        return $this->trackCustomEvent('conversion', [
            'conversion_id' => $conversionId,
            'value' => $value,
            'currency' => 'USD'  // Change as needed
        ]);
    }
}