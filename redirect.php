<?php
// Check if 'id' parameter is present in the URL
if (!isset($_GET['id'])) {
    // If 'id' parameter is missing, redirect to a 404 or error page
    header("Location: /404.html");
    exit;
}

// Get the Instagram post or reel ID from the query parameter and sanitize it
$postId = htmlspecialchars($_GET['id']);

// Define the Instagram app and web URLs
$instagramAppUrl = "instagram://media?id=" . $postId;

// Check if the ID is a post or a reel by inspecting the first character of the ID
// This is a simple way to differentiate, assuming IDs follow predictable patterns
$instagramWebUrl = "https://www.instagram.com/p/" . $postId . "/";
if (strpos($postId, "reel") === 0) {
    $instagramWebUrl = "https://www.instagram.com/reel/" . $postId . "/";
}

// Redirect to the Instagram app URL first
header("Location: $instagramAppUrl");

// Set a fallback to the web version if the app isn't available
header("Refresh: 1; url=$instagramWebUrl");

exit;
