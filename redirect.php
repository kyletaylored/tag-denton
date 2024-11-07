<?php

require 'vendor/autoload.php';

use TheIconic\Tracking\GoogleAnalytics\Analytics;

// Function to track visits with Google Analytics
function trackVisit($id) {
    $analytics = new Analytics(true); // Enable SSL
    $analytics
        ->setProtocolVersion('1')
        ->setTrackingId($_ENV['GA_ID']) // Replace with your Google Analytics tracking ID
        ->setClientId($_SERVER['REMOTE_ADDR']) // Using IP as unique client identifier for simplicity
        ->setEventCategory('Visit')
        ->setEventAction('Landmark Scanned')
        ->setEventLabel($id)
        ->sendEvent();
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
