<?php

namespace App\Controllers;

use App\Helpers\EnvConfig;

class MixpanelProvider implements AnalyticsProviderInterface
{
    private string $projectToken;
    private string $endpoint = 'https://api.mixpanel.com/track';
    private bool $enabled = false;

    public function __construct()
    {
        try {
            $this->projectToken = EnvConfig::get('MIXPANEL_PROJECT_TOKEN');
            $this->enabled = !empty($this->projectToken);
        } catch (\Exception $e) {
            error_log("Mixpanel initialization failed: " . $e->getMessage());
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    private function generateDistinctId(): string
    {
        return hash('sha256', uniqid('mp_', true));
    }

    public function sendEvent(string $eventName, array $properties): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $payload = [
            'event' => $eventName,
            'properties' => array_merge(
                [
                    'distinct_id' => $this->generateDistinctId(),
                    'token' => $this->projectToken,
                    'time' => time()
                ],
                $properties
            )
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([$payload]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: text/plain',
                'User-Agent: PHP Mixpanel Client/1.0'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && $response === '1';
    }
}