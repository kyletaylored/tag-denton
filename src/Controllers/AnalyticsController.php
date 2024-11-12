<?php

namespace App\Controllers;

use AlexWestergaard\PhpGa4\Analytics;
use AlexWestergaard\PhpGa4\Item;
use AlexWestergaard\PhpGa4\Events\SelectItem;

class AnalyticsController
{
    /**
     * Generate a unique client ID based on IP, user-agent, and timestamp.
     *
     * @return string
     */
    public function generateClientId()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown_ip';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown_agent';
        $timestamp = microtime(true);

        // Combine the IP, User-Agent, and a high-precision timestamp to create a unique hash
        return hash('sha256', $ip . $userAgent . $timestamp);
    }

    /**
     * Track visits using Google Analytics 4.
     *
     * @param string $id The unique ID for the event (e.g., redirect key or item ID).
     * @return void
     */
    public function trackVisit(string $id)
    {
        // Pull the Measurement ID and API Secret from environment variables
        $measurementId = getenv('GA_MEASUREMENT_ID');
        $apiSecret = getenv('GA_API_SECRET');

        // If either the Measurement ID or API Secret is not set, skip tracking
        if (!$measurementId || !$apiSecret) {
            error_log('GA4 tracking skipped: missing credentials.');
            return;
        }

        // Generate a unique client ID
        $clientId = $this->generateClientId();

        try {
            // Initialize the Analytics object
            $analytics = Analytics::new($measurementId, $apiSecret, debug: false)
                ->setClientId($clientId);

            // Create an Item object representing the media
            $item = Item::new()
                ->setItemId($id)
                ->setItemName('Media ' . $id);

            // Create a SelectItem event
            $event = SelectItem::new()
                ->addItem($item); // Add the Item object as an event parameter

            // Add the event to analytics
            $analytics->addEvent($event);

            // Send the event to GA4
            $analytics->post();
        } catch (\Exception $e) {
            // Log any errors during the GA4 tracking process
            error_log('GA4 tracking failed: ' . $e->getMessage());
        }
    }
}
