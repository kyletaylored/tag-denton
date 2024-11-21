<?php

namespace App\Controllers;

use App\Helpers\EnvConfig;

class GoogleAnalyticsProvider implements AnalyticsProviderInterface
{
    private string $measurementId;
    private string $apiSecret;
    private string $endpoint = 'https://www.google-analytics.com/mp/collect';
    private bool $enabled = false;

    public function __construct()
    {
        try {
            $this->measurementId = EnvConfig::get('GA_MEASUREMENT_ID');
            $this->apiSecret = EnvConfig::get('GA_API_SECRET');
            $this->enabled = !empty($this->measurementId) && !empty($this->apiSecret);
        } catch (\Exception $e) {
            error_log("GA initialization failed: " . $e->getMessage());
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    private function generateClientId(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown_ip';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown_agent';
        $timestamp = microtime(true);
        return hash('sha256', $ip . $userAgent . $timestamp);
    }

    public function sendEvent(string $eventName, array $properties): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $payload = [
            'client_id' => $this->generateClientId(),
            'events' => [
                [
                    'name' => $eventName,
                    'params' => $properties
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

        return $httpCode === 204;
    }
}
