<?php

/**
 * @package   Enlivenapp\FlightCsrf
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

if (!function_exists('csrf_field')) {
    /**
     * Output a hidden CSRF token input field.
     *
     * @return string HTML hidden input element
     */
    function csrf_field(): string
    {
        $config = \Flight::app()->get('enlivenapp.flight-csrf') ?? [];
        $fieldName = $config['field_name'] ?? '_csrf_token';

        $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="' . $fieldName . '" value="' . $token . '">';
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the current CSRF token value. Generates a new token if one
     * doesn't exist, or if the existing token has exceeded token_lifetime.
     *
     * @return string The token string
     */
    function csrf_token(): string
    {
        $config = \Flight::app()->get('enlivenapp.flight-csrf') ?? [];
        $sessionKey = $config['session_key'] ?? '_csrf_token';
        $lifetime = (int)($config['token_lifetime'] ?? 0);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $data = $_SESSION[$sessionKey] ?? null;
        $now = time();

        $valid = is_array($data)
            && isset($data['token'], $data['created'])
            && ($lifetime === 0 || ($now - (int)$data['created']) < $lifetime);

        if (!$valid) {
            $data = [
                'token'   => bin2hex(random_bytes(32)),
                'created' => $now,
            ];
            $_SESSION[$sessionKey] = $data;
        }

        return $data['token'];
    }
}
