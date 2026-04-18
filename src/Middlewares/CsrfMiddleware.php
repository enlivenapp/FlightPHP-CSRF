<?php

/**
 * @package   Enlivenapp\FlightCsrf
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightCsrf\Middlewares;

use flight\Engine;

class CsrfMiddleware
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function before(): void
    {
        $config = $this->app->get('enlivenapp.flight-csrf') ?? [];
        $fieldName = $config['field_name'] ?? '_csrf_token';
        $sessionKey = $config['session_key'] ?? '_csrf_token';
        $protectedMethods = $config['protected_methods'] ?? ['POST', 'PUT', 'PATCH', 'DELETE'];
        $excludeRoutes = $config['exclude_routes'] ?? [];
        $lifetime = (int)($config['token_lifetime'] ?? 0);

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (!in_array($method, $protectedMethods, true)) {
            return;
        }

        $requestUri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
        if (in_array($requestUri, $excludeRoutes, true)) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sessionData = $_SESSION[$sessionKey] ?? null;
        $sessionToken = null;
        $expired = true;

        if (is_array($sessionData) && isset($sessionData['token'], $sessionData['created'])) {
            $sessionToken = $sessionData['token'];
            $expired = $lifetime !== 0 && (time() - (int)$sessionData['created']) >= $lifetime;
        }

        $submittedToken = $_POST[$fieldName]
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? null;

        if ($sessionToken === null || $submittedToken === null || $expired || !hash_equals($sessionToken, $submittedToken)) {
            $this->app->halt(403, json_encode([
                'error' => 'CSRF token validation failed.',
            ]));
        }
    }
}
