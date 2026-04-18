<?php

/**
 * @package   Enlivenapp\FlightCsrf
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

return [
    // Name of the hidden form field
    'field_name' => '_csrf_token',

    // Session key for storing the token
    'session_key' => '_csrf_token',

    // HTTP methods that require CSRF validation
    'protected_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],

    // Routes to exclude from CSRF validation (exact match)
    'exclude_routes' => [],

    // Absolute token lifetime in seconds from creation. Default: 7200 (2 hours).
    // Set to 0 to disable time-based expiry (token lives for the session).
    // Expired tokens fail validation and rotate on next read. Override per-app
    // via the plugins array in app/config/config.php.
    'token_lifetime' => 7200,
];
