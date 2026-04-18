<?php

/**
 * @package   Enlivenapp\FlightCsrf
 * @copyright 2026 enlivenapp
 * @license   MIT
 */

declare(strict_types=1);

namespace Enlivenapp\FlightCsrf;

use Enlivenapp\FlightSchool\PluginInterface;
use flight\Engine;
use flight\net\Router;

class Plugin implements PluginInterface
{
    public function register(Engine $app, Router $router, array $config = []): void
    {
        $this->ensureAppConfig();
        require_once __DIR__ . '/Helpers/csrf_helper.php';
    }

    /**
     * Ensure overridable config entries exist under the plugin's block
     * in app/config/config.php. Adds missing keys with defaults so users
     * can see and edit them in place.
     */
    protected function ensureAppConfig(): void
    {
        $configFile = defined('PROJECT_ROOT')
            ? PROJECT_ROOT . '/app/config/config.php'
            : null;

        if ($configFile === null || !file_exists($configFile)) {
            return;
        }

        $contents = file_get_contents($configFile);
        if ($contents === false) {
            return;
        }

        // Find the flight-csrf plugin entry and its block
        $pluginPos = strpos($contents, "'enlivenapp/flight-csrf'");
        if ($pluginPos === false) {
            return;
        }

        $openPos = strpos($contents, '[', $pluginPos);
        if ($openPos === false) {
            return;
        }

        $depth = 0;
        $closePos = false;
        $len = strlen($contents);
        for ($i = $openPos; $i < $len; $i++) {
            if ($contents[$i] === '[') {
                $depth++;
            } elseif ($contents[$i] === ']') {
                $depth--;
                if ($depth === 0) {
                    $closePos = $i;
                    break;
                }
            }
        }

        if ($closePos === false) {
            return;
        }

        // Only check within the plugin's own block to avoid false positives
        $block = substr($contents, $openPos, $closePos - $openPos + 1);

        $blocks = [];
        if (!str_contains($block, "'token_lifetime'")) {
            $blocks[] = "\t\t\t'token_lifetime' => 7200,";
        }
        if (!str_contains($block, "'exclude_routes'")) {
            $blocks[] = "\t\t\t'exclude_routes' => [],";
        }

        if (empty($blocks)) {
            return;
        }

        $before = substr($contents, 0, $closePos);
        $trimmed = rtrim($before);
        if (!str_ends_with($trimmed, ',')) {
            $trimmed .= ',';
        }

        $contents = $trimmed . "\n" . implode("\n", $blocks) . "\n\t\t" . substr($contents, $closePos);

        file_put_contents($configFile, $contents);
    }
}
