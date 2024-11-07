<?php

require 'vendor/autoload.php';

use AlexWestergaard\PhpGa4\Analytics;
use AlexWestergaard\PhpGa4\Item;
use AlexWestergaard\PhpGa4\Event\SelectItem;

/**
 * Generate a client id.
 *
 * @return string
 */
function generateClientId() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown_ip';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown_agent';
    $timestamp = microtime(true);

    // Combine the IP, User-Agent, and a high-precision timestamp to create a unique hash
    return hash('sha256', $ip . $userAgent . $timestamp);
}

// Function to track visits with Google Analytics 4
function trackVisit($id) {
    // Pull the Measurement ID and API Secret from environment variables
    $measurementId = getenv('GA_MEASUREMENT_ID');
    $apiSecret = getenv('GA_API_SECRET');

    // If either the Measurement ID or API Secret is not set, skip tracking
    if (!$measurementId || !$apiSecret) return;

    // Generate a unique client ID based on IP, user-agent, and timestamp
    $clientId = generateClientId();

    // Initialize the Analytics object
    $analytics = Analytics::new(
        measurementId: $measurementId,
        apiSecret: $apiSecret,
        debug: false // Set to true for debugging
    )
    ->setClientId($clientId)
    ->setTimestampMicros((int)(microtime(true) * 1000000));

    // Create an Item object representing the media
    $item = Item::new()
        ->setItemId($id)
        ->setItemName('Media ' . $id);

    // Create a SelectItem event
    $event = SelectItem::new()
        ->setItem($item); // Add the Item object as an event parameter

    // Add the event to analytics
    $analytics->addEvent($event);

    // Send the event to GA4
    $analytics->post();
}

// Check if 'id' parameter is present in the URL
if (!isset($_GET['id'])) {
    header("Location: /404.html");
    exit;
}

// Get the Instagram post or reel ID from the query parameter and sanitize it
$postId = htmlspecialchars($_GET['id']);

// Track the visit in Google Analytics
trackVisit($postId);

// Define the Instagram app and web URLs
$instagramAppUrl = "instagram://media?id=" . $postId;

// Determine whether it's a post or a reel URL
$instagramWebUrl = "https://www.instagram.com/p/" . $postId . "/";
if (strpos($postId, "reel") === 0) {
    $instagramWebUrl = "https://www.instagram.com/reel/" . $postId . "/";
}

// Redirect to the Instagram app URL first
header("Location: $instagramAppUrl");

// Set a fallback to the web version if the app isn't available
header("Refresh: 1; url=$instagramWebUrl");

exit;
