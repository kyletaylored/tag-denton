<?php
// Check if 'id' parameter is present in the URL
if (!isset($_GET['id'])) {
    // If 'id' parameter is missing, redirect to a 404 or error page
    header("Location: /404.html");
    exit;
}

// Get the Instagram post ID from the query parameter
$postId = htmlspecialchars($_GET['id']);

// Define the Instagram app and web URLs using the post ID
$instagramAppUrl = "instagram://media?id=" . $postId;
$instagramWebUrl = "https://www.instagram.com/p/" . $postId . "/";

// Attempt to redirect to the Instagram app URL
header("Location: $instagramAppUrl");

// Set a fallback refresh to the Instagram web URL in case the app isn't installed
header("Refresh: 1; url=$instagramWebUrl");

exit;
