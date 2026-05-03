[![Stable? Not Quite Yet](https://img.shields.io/badge/stable%3F-not%20quite%20yet-blue?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-csrf)
[![License](https://img.shields.io/packagist/l/enlivenapp/flight-csrf?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-csrf)
[![PHP Version](https://img.shields.io/packagist/php-v/enlivenapp/flight-csrf?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-csrf)
[![Monthly Downloads](https://img.shields.io/packagist/dm/enlivenapp/flight-csrf?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-csrf)
[![Total Downloads](https://img.shields.io/packagist/dt/enlivenapp/flight-csrf?style=for-the-badge)](https://packagist.org/packages/enlivenapp/flight-csrf)
[![GitHub Issues](https://img.shields.io/github/issues/enlivenapp/FlightPHP-CSRF?style=for-the-badge)](https://github.com/enlivenapp/FlightPHP-CSRF/issues)
[![Contributors](https://img.shields.io/github/contributors/enlivenapp/FlightPHP-CSRF?style=for-the-badge)](https://github.com/enlivenapp/FlightPHP-CSRF/graphs/contributors)
[![Latest Release](https://img.shields.io/github/v/release/enlivenapp/FlightPHP-CSRF?style=for-the-badge)](https://github.com/enlivenapp/FlightPHP-CSRF/releases)
[![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-blue?style=for-the-badge)](https://github.com/enlivenapp/FlightPHP-CSRF/pulls)


# flight-csrf

**I noticed folks downloading some of these packages. I'm super grateful, Thank You!  I would like to let folks know until this notice disappears I'm doing a lot of breaking changes without worrying about them.  Once versions are up around 0.5.x things should settle down.**

CSRF protection middleware and helpers for [FlightPHP](https://flightphp.com), built for the [flight-school](https://github.com/enlivenapp/flight-school) plugin system.

## Requirements

- PHP 8.1+
- `enlivenapp/flight-school` ^0.2

## Installation

```bash
composer require enlivenapp/flight-csrf
```

Flight School auto-discovers the package via its `flightphp-plugin` composer type. Enable it in your app config:

```php
// app/config/config.php
return [
    // ...
    'plugins' => [
        'enlivenapp/flight-csrf' => [
            'enabled' => true,
        ],
    ],
];
```

## Usage

### Forms

Use `csrf_field()` to output a hidden input in any HTML form. The token is generated and stored in the session automatically.

```html+php
<form method="POST" action="/login">
    <?= csrf_field() ?>
    <input type="text" name="username">
    <button type="submit">Log in</button>
</form>
```

### AJAX requests

Use `csrf_token()` to retrieve the raw token value. The most common pattern is to embed it in a meta tag and read it from JavaScript.

```html+php
<meta name="csrf-token" content="<?= csrf_token() ?>">
```

```javascript
fetch('/api/resource', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ foo: 'bar' }),
});
```

The middleware accepts the token from either the `_csrf_token` POST field or the `X-CSRF-TOKEN` request header (POST field is checked first).

### Protecting routes

Apply `CsrfMiddleware` to any route or route group that should be protected:

```php
use Enlivenapp\FlightCsrf\Middlewares\CsrfMiddleware;

Flight::route('POST /login', function () {
    // ...
})->addMiddleware(new CsrfMiddleware(Flight::app()));

// Or on a group
Flight::group('/admin', function () {
    Flight::route('POST /save', function () { /* ... */ });
    Flight::route('DELETE /remove/@id', function ($id) { /* ... */ });
}, [new CsrfMiddleware(Flight::app())]);
```

When validation fails the middleware halts the request with a `403` response. The body is a JSON-encoded error payload (`{"error":"CSRF token validation failed."}`); note that no `Content-Type` header is set on the halted response.

## Configuration

Configuration is stored under the `enlivenapp.flight-csrf` key. The plugin's own defaults live in `src/Config/Config.php`; override them per-app via the `plugins` array in `app/config/config.php` (values are merged on top of the defaults with `array_replace_recursive`).

| Key | Default | Description |
|---|---|---|
| `field_name` | `_csrf_token` | Name of the hidden form field |
| `session_key` | `_csrf_token` | Session key used to store the token |
| `protected_methods` | `['POST','PUT','PATCH','DELETE']` | HTTP methods that trigger validation |
| `exclude_routes` | `[]` | Request paths to skip (exact URI match, no query string) |
| `token_lifetime` | `7200` | Absolute seconds from creation before the token expires (default: 2 hours). `0` disables time-based expiry (token lives for the session). Expired tokens fail validation (403) and are rotated on the next `csrf_token()` / `csrf_field()` call. |

### Overriding defaults

On first load the plugin scaffolds `token_lifetime` and `exclude_routes` into its block in `app/config/config.php` so you can edit them in place. Any other config key can also be added manually to the same block — values are merged on top of the plugin defaults.

Example after scaffolding, with a shorter token lifetime and exclude routes filled in:

```php
// app/config/config.php
return [
    // ...
    'plugins' => [
        'enlivenapp/flight-csrf' => [
            'enabled' => true,
            'token_lifetime' => 3600,   // 1 hour instead of the 2-hour default
            'exclude_routes' => [
                '/webhooks/stripe',
                '/api/public/callback',
            ],
        ],
    ],
];
```

## License

MIT
