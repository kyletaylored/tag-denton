<?php

namespace App\Controllers;

use App\Helpers\EnvConfig;

class AnalyticsController
{
    private array $providers = [];
    private array $propertyMap = [
        'google' => [
            'redirect_key' => 'redirect_key',
            'platform' => 'platform',
            'device_type' => 'device_type',
            'browser' => 'browser',
            'os' => 'os',
            'target_url' => 'target_url',
            'original_url' => 'original_url',
            'country' => 'country',
            'region' => 'region',
            'city' => 'city'
        ],
        'mixpanel' => [
            'redirect_key' => 'Redirect Key',
            'platform' => 'Platform',
            'device_type' => 'Device Type',
            'browser' => 'Browser',
            'os' => 'OS',
            'target_url' => 'Target URL',
            'original_url' => 'Original URL',
            'country' => 'Country',
            'region' => 'Region',
            'city' => 'City'
        ]
    ];

    public function __construct()
    {
        // Initialize configured providers
        if (EnvConfig::get('ANALYTICS_PROVIDER') === 'google') {
            $this->providers[] = new GoogleAnalyticsProvider();
        } elseif (EnvConfig::get('ANALYTICS_PROVIDER') === 'mixpanel') {
            $this->providers[] = new MixpanelProvider();
        } elseif (EnvConfig::get('ANALYTICS_PROVIDER') === 'all') {
            $this->providers[] = new GoogleAnalyticsProvider();
            $this->providers[] = new MixpanelProvider();
        }
    }

    public function trackRedirectEvent(string $key, array $deviceData, array $geoData, string $redirectUrl, string $originalUrl): void
    {
        // Normalize the data
        $normalizedData = [
            'redirect_key' => $key,
            'platform' => $deviceData['os']['family'] ?? 'unknown',
            'device_type' => $deviceData['device']['type'] ?? 'unknown',
            'browser' => $deviceData['client']['name'] ?? 'unknown',
            'os' => $deviceData['os']['name'] ?? 'unknown',
            'target_url' => $redirectUrl,
            'original_url' => $originalUrl,
            'country' => $geoData['country'] ?? 'unknown',
            'region' => $geoData['region'] ?? 'unknown',
            'city' => $geoData['city'] ?? 'unknown'
        ];

        foreach ($this->providers as $provider) {
            if ($provider->isEnabled()) {
                // Map the normalized data to provider-specific format
                $providerData = $this->mapPropertiesToProvider(
                    $normalizedData,
                    $provider instanceof GoogleAnalyticsProvider ? 'google' : 'mixpanel'
                );
                
                $provider->sendEvent('app_redirect', $providerData);
            }
        }
    }

    private function mapPropertiesToProvider(array $normalizedData, string $provider): array
    {
        $mappedData = [];
        foreach ($normalizedData as $key => $value) {
            if (isset($this->propertyMap[$provider][$key])) {
                $mappedData[$this->propertyMap[$provider][$key]] = $value;
            }
        }
        return $mappedData;
    }

    public function getDebugInfo(): array
    {
        $debug = [];
        foreach ($this->providers as $provider) {
            $type = get_class($provider);
            $debug[$type] = [
                'enabled' => $provider->isEnabled(),
                'type' => $type
            ];
        }
        return $debug;
    }
}