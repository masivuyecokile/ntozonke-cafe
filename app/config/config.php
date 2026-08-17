<?php

define('CLIENT_ONLINE_THRESHOLD_SECONDS', 45);
date_default_timezone_set('Africa/Johannesburg');

define('APP_NAME', 'Internet Cafe Management');
define('BUSINESS_NAME', 'Ntozonke Internet Cafe');

date_default_timezone_set('Africa/Johannesburg');

/**
 * Dynamic base URL.
 * 
 * Laptop:
 * http://localhost:8089
 *
 * Phone / client PCs:
 * http://192.168.18.4:8089
 */
$scheme = 'http';

if (
    isset($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== 'off'
) {
    $scheme = 'https';
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8089';

define('BASE_URL', $scheme . '://' . $host);