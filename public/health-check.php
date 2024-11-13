<?php
// health-check.php
$checks = [
    'php-fpm' => false,
    'nginx' => false,
    'filesystem' => false
];

// Check PHP-FPM
$checks['php-fpm'] = true; // If this script runs, PHP-FPM is working

// Check Nginx
$checks['nginx'] = isset($_SERVER['SERVER_SOFTWARE']) && 
                  strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false;

// Check filesystem
$checks['filesystem'] = is_writable('/var/www/html');

// If any check fails, return 500
if (in_array(false, $checks, true)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'checks' => $checks]);
    exit(1);
}

// All checks passed
echo json_encode(['status' => 'healthy', 'checks' => $checks]);