# Bachs for Laravel

Laravel integration for [`zeevx/php-bachs`](https://github.com/zeevx/php-bachs). It provides automatic package discovery, container bindings, a facade, publishable configuration, signature verification, a webhook route, and a framework event while leaving billing tables and business rules in your application.

## Requirements

- PHP 8.2 or newer
- Laravel 11, 12, or 13
- `zeevx/php-bachs` 1.x

## Installation

```bash
composer require zeevx/laravel-bachs
php artisan vendor:publish --tag=bachs-config
```

Laravel discovers `Zeevx\LaravelBachs\BachsServiceProvider` automatically.

## Environment

```dotenv
BACHS_API_KEY=
BACHS_BASE_URL=https://sandbox-api.bachs.io
BACHS_TIMEOUT=30

BACHS_WEBHOOK_SECRET=
BACHS_WEBHOOK_TOLERANCE=300
BACHS_WEBHOOK_PATH=bachs/webhook
BACHS_WEBHOOK_ROUTE_ENABLED=true
```

Use `https://api.bachs.io` for production. Keep the API key and webhook secret server-side and never expose them to browser JavaScript.

## Configuration

The published `config/bachs.php` contains:

```php
return [
    'api_key' => env('BACHS_API_KEY'),
    'base_url' => env('BACHS_BASE_URL', 'https://api.bachs.io'),
    'timeout' => (int) env('BACHS_TIMEOUT', 30),

    'webhook' => [
        'secret' => env('BACHS_WEBHOOK_SECRET'),
        'tolerance' => (int) env('BACHS_WEBHOOK_TOLERANCE', 300),
        'path' => env('BACHS_WEBHOOK_PATH', 'bachs/webhook'),
        'route_enabled' => env('BACHS_WEBHOOK_ROUTE_ENABLED', true),
    ],
];
```

Disable the included webhook route when your application needs to register a custom controller or route group.

## Resolving the client

Use dependency injection:

```php
use Zeevx\Bachs\Bachs;

final readonly class CreateCheckout
{
    public function __construct(private Bachs $bachs) {}

    public function handle(): string
    {
        $checkout = $this->bachs->checkoutSessions()->create([
            'product_cart' => [['product_id' => 'product_123', 'quantity' => 1]],
            'customer' => [
                'email' => 'ada@example.com',
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
            ],
            'billing_currency' => 'USD',
        ]);

        return (string) $checkout->url;
    }
}
```

Or use the facade:

```php
use Zeevx\LaravelBachs\Facades\Bachs;

$customer = Bachs::customers()->create([
    'email' => 'ada@example.com',
    'name' => 'Ada Lovelace',
]);
```

All SDK resources are available:

```php
$bachs->checkoutSessions()->create($payload);
$bachs->checkoutSessions()->retrieve($checkoutId);

$bachs->customers()->create($payload);
$bachs->customers()->retrieve($customerId);
$bachs->customers()->update($customerId, $payload);
$bachs->customers()->createPortalSession($customerId, $payload);

$bachs->subscriptions()->retrieve($subscriptionId);
$bachs->subscriptions()->update($subscriptionId, $payload);
$bachs->subscriptions()->cancel($subscriptionId);
```

See the [PHP SDK documentation](https://github.com/zeevx/php-bachs) for response objects, exceptions, and complete examples.

## Webhooks

By default, the package registers:

```text
POST /bachs/webhook
```

The `VerifyWebhookSignature` middleware:

1. Reads the exact raw request body.
2. Requires `X-Bachs-Timestamp` and `X-Bachs-Signature`.
3. Rejects requests outside the configured tolerance.
4. Verifies the HMAC SHA-256 signature with `BACHS_WEBHOOK_SECRET`.
5. Parses the verified payload into a typed `WebhookEvent`.

Invalid requests receive a generic HTTP 400 response. Valid requests dispatch `Zeevx\LaravelBachs\Events\WebhookReceived` and receive HTTP 204.

If the route uses Laravel's `web` middleware, exempt the configured path from CSRF protection in your application bootstrap:

```php
$middleware->validateCsrfTokens(except: [
    'bachs/webhook',
]);
```

## Handling events

Register a listener in your application:

```php
use Illuminate\Support\Facades\Event;
use App\Listeners\ProcessBachsWebhook;
use Zeevx\LaravelBachs\Events\WebhookReceived;

Event::listen(WebhookReceived::class, ProcessBachsWebhook::class);
```

A queued listener keeps webhook responses fast:

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Zeevx\LaravelBachs\Events\WebhookReceived;

final class ProcessBachsWebhook implements ShouldQueue
{
    public function handle(WebhookReceived $received): void
    {
        $event = $received->event;

        $event->id;
        $event->type;
        $event->data;
        $event->payload;
    }
}
```

Bachs delivers webhooks at least once. Store the event ID under a unique constraint and make handlers idempotent before granting credits, activating subscriptions, or changing entitlements.

## Application responsibilities

This package intentionally does not provide models, migrations, plan configuration, or a Cashier-style billing schema. Your application should own:

- Bachs customer IDs associated with local users or teams
- Provider-neutral subscription records and statuses
- Product-to-plan mappings and feature entitlements
- Currency policy and allowed payment methods
- Checkout references and metadata
- Webhook event idempotency and retry state
- One-time purchase fulfilment and ledgers
- Subscription migration from a previous provider

Keeping those rules outside the integration package makes the package reusable and prevents provider details from becoming your application domain model.

## Testing

The package's Testbench suite verifies container registration, webhook signatures, event dispatch, and invalid-request handling.

In an application test, fake the framework event:

```php
use Illuminate\Support\Facades\Event;
use Zeevx\LaravelBachs\Events\WebhookReceived;

Event::fake([WebhookReceived::class]);
```

For API client tests, inject or replace the `Zeevx\Bachs\Bachs` binding with a client configured with Guzzle's `MockHandler`.

## Development

```bash
composer install
composer test
composer analyse
composer format
```

The package uses Pest, Orchestra Testbench, PHPStan level 8, Laravel Pint, strict types, and GitHub Actions across supported PHP and Laravel versions.

## License

The MIT License. See [LICENSE](LICENSE).
