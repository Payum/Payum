# Dependency Injection - Getting Started

## Overview

Starting with Payum v2.0, the library uses a modern dependency injection (DI) container system powered by [PHP-DI 7.0](https://php-di.org/). This replaces the old ArrayObject-based configuration with a more maintainable, type-safe approach.

## Key Benefits

- **Type Safety**: Services are properly typed and autowired
- **Shared Services**: HTTP clients and other services are shared across all gateways
- **Customization**: Easy to override default services or add your own
- **Framework Integration**: Compatible with Symfony, Laravel, and other frameworks
- **Performance**: Services are lazy-loaded and reused where appropriate

## Architecture Overview

Payum v2.0 uses a **hybrid container architecture**:

```
┌─────────────────────────────────────────────┐
│     Global Container (Payum-wide)           │
│  - HttpRequestVerifier                      │
│  - GenericTokenFactory                      │
│  - TokenFactory                             │
│  - TokenStorage                             │
│  - Storage Extensions                       │
│  - PSR-18 HTTP Client (shared)              │
│  - PSR-17 Factories                         │
│  - Anything from addGlobalService()         │
└──────────────┬──────────────────────────────┘
               │ delegated to
        ┌──────┼──────┬──────┐
        ▼      ▼      ▼      ▼
    ┌────────┬───────┬───────┬────────┐
    │Gateway │Gateway│Gateway│Gateway │
    │stripe  │paypal │offline│custom  │
    └────────┴───────┴───────┴────────┘
```

- **Global Container**: Holds services shared across all gateways (HTTP client, token storage, etc.)
- **Per-Gateway Containers**: One container per gateway, holding the gateway's own services (API clients,
  actions, configuration). The shared services are available here too — asking a gateway container for, say,
  the PSR-18 client gives you the one and only instance from the global container.

## Basic Usage

### Using PayumBuilder (Recommended)

The easiest way to use Payum with DI is through the `PayumBuilder`:

```php
<?php

use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()

    ->addGateway('stripe', [
        'factory' => 'stripe_checkout',
        'publishable_key' => 'pk_test_...',
        'secret_key' => 'sk_test_...',
    ])

    ->addGateway('paypal', [
        'factory' => 'paypal_rest',
        'client_id' => 'your-client-id',
        'client_secret' => 'your-client-secret',
        'sandbox' => true,
    ])

    ->getPayum();

// Use the gateway
$gateway = $payum->getGateway('stripe');
$gateway->execute(new Capture($payment));
```

### Adding Global Services

You can add services that will be available to all gateways:

```php
<?php

use Psr\Log\LoggerInterface;
use Monolog\Logger;

$logger = new Logger('payum');

$payum = (new PayumBuilder())
    ->addDefaultStorages()

    // Add a global service
    ->addGlobalService(LoggerInterface::class, $logger)

    ->addGateway('stripe', [
        'factory' => 'stripe_checkout',
        // ... config
    ])

    ->getPayum();
```

### Overriding Default Services

You can override default services like the HTTP client:

```php
<?php

use Psr\Http\Client\ClientInterface;

$customHttpClient = new MyCustomHttpClient();

$payum = (new PayumBuilder())
    ->addDefaultStorages()

    // Override the default HTTP client
    ->addGlobalService(ClientInterface::class, $customHttpClient)

    ->addGateway('stripe', ['factory' => 'stripe_checkout', /* ... */])
    ->getPayum();
```

## Understanding the Container System

### Global Services

These services are instantiated once and shared across all gateways:

- `payum.security.token_storage` - Token storage
- `HttpRequestVerifierInterface` - Verifies security tokens
- `GenericTokenFactoryInterface` - Creates payment tokens
- `TokenFactoryInterface` - Low-level token factory
- `ClientInterface` (PSR-18) - HTTP client
- `StreamFactoryInterface` (PSR-17) - HTTP stream factory
- `RequestFactoryInterface` (PSR-17) - HTTP request factory
- Storage extensions for all registered models
- Every service registered with `addGlobalService()`

### Per-Gateway Services

These services are specific to each gateway:

- API client classes (e.g., `Stripe\Api\Keys`)
- Action classes (e.g., `CaptureAction`, `StatusAction`)
- Gateway-specific configuration

### Service Resolution Order

Each gateway container is assembled from three layers. Later layers win:

1. The gateway factory's `configureContainer()` definitions (the factory's own defaults)
2. The shared services listed above (so a global service always wins over a factory default)
3. The config passed to `addGateway()` (so a single gateway can override anything)

Anything not defined in any of those layers is autowired by PHP-DI.

## Writing a Gateway Factory

A gateway factory declares the services its gateway needs and which of them are actions and extensions.
Extend `CoreGatewayFactory` and override the methods you care about:

```php
<?php

use Payum\Core\CoreGatewayFactory;
use function DI\autowire;
use function DI\get;

class MyGatewayFactory extends CoreGatewayFactory
{
    public function configureContainer(): array
    {
        return array_merge(parent::configureContainer(), [
            'my_gateway.client_id' => '',
            'my_gateway.secret' => '',

            Api::class => autowire()
                ->constructor(
                    get('my_gateway.client_id'),
                    get('my_gateway.secret')
                ),

            CaptureAction::class => autowire(),
            StatusAction::class => autowire(),
        ]);
    }

    public function getActions(): array
    {
        return array_merge(parent::getActions(), [
            CaptureAction::class,
            StatusAction::class,
        ]);
    }
}
```

`CoreGatewayFactory` already implements `ContainerConfiguration`, so extending it is enough — there is no
need to add `implements ContainerConfiguration` yourself.

### Extension Points

| Method | Purpose |
|---|---|
| `configureContainer(): array` | Return the PHP-DI definitions for the gateway (config values, API classes, actions). |
| `getActions(): array` | Return the **class names** of the actions to register on the gateway, in order. |
| `getExtensions(): array` | Return the extensions to register on the gateway. |

`createGateway()` resolves everything `getActions()` and `getExtensions()` return from the container and
registers it on a fresh `Gateway`. Actions must be given as class strings; extensions may be either class
strings or ready-made instances.

Actions and extensions are appended in the order returned. If a service has to run *before* everything
else, mark its class with `Payum\Core\Action\PrependActionInterface` or
`Payum\Core\Extension\PrependExtensionInterface` and it will be prepended instead:

```php
<?php

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\PrependActionInterface;

class MyEarlyAction implements ActionInterface, PrependActionInterface
{
    // ... always consulted before the actions registered ahead of it
}
```

You only need to override `createGateway()` when you have to do something to the `Gateway` itself that
`getActions()`/`getExtensions()` cannot express.

### Registering the Factory

Register the factory under a name, then reference that name from `addGateway()`:

```php
<?php

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGatewayFactory('my_gateway', new MyGatewayFactory())
    ->addGateway('mine', [
        'factory' => 'my_gateway',

        // These override the defaults declared in configureContainer()
        'my_gateway.client_id' => 'theClientId',
        'my_gateway.secret' => 'theSecret',
    ])
    ->getPayum();
```

Coming from Payum v1.x? The [Migration Guide](migration-guide.md) shows how the old `populateConfig()`
style maps onto this one.

## Next Steps

- [Customization Guide](customization.md) - Learn how to customize services
- [Migration Guide](migration-guide.md) - Migrate your code from v1.x to v2.0
- [Framework Integration](framework-integration.md) - Integrate with Symfony/Laravel
- [The Architecture](../the-architecture.md) - Deep dive into Payum's architecture

## Need Help?

- 📖 [Full Documentation](https://payum.gitbook.io/payum/)
- 🐛 [Report Issues](https://github.com/Payum/Payum/issues)
- 💬 [Community Support](https://github.com/Payum/Payum/discussions)
