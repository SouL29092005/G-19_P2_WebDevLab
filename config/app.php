<?php

declare(strict_types=1);

define('APP_NAME', 'Laboratory Computer Reservation System');

define('APP_BASE_URL', 'http://lab-reservation.local:8080');

define('APP_TIMEZONE', 'Asia/Kolkata');

$environment = getenv('APP_ENV');

define(
    'APP_ENV',
    $environment !== false && $environment !== ''
        ? $environment
        : 'development'
);

define(
    'APP_DEBUG',
    APP_ENV === 'development'
);

date_default_timezone_set(APP_TIMEZONE);

ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('display_startup_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

define('SESSION_NAME', 'LAB_RESERVATION_SESSION');

$isHttps = isset($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== ''
    && strtolower($_SERVER['HTTPS']) !== 'off';

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');

session_name(SESSION_NAME);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

define('PROJECT_ROOT', dirname(__DIR__));

define('APP_PATH', PROJECT_ROOT . '/app');
define('CONFIG_PATH', PROJECT_ROOT . '/config');
define('PUBLIC_PATH', PROJECT_ROOT . '/public');
define('DATABASE_PATH', PROJECT_ROOT . '/database');
