# Migration Guide: v1.x to v2.0

## Overview

Payum v2.0 introduces a modern dependency injection (DI) container system. This guide helps you migrate your code from the old ArrayObject-based configuration to the new DI-based approach.

**Good news:** Your existing code will continue to work in v2.0! The old pattern is deprecated but still functional, giving you time to migrate gradually.

## Migration Timeline

- **v2.0 (current)**: Both the old and the new pattern work
- **v3.0**: The old pattern is removed — see [Deprecated APIs](#deprecated-apis) for the full list

## For PayumBuilder Users

If you're using `PayumBuilder` (the recommended approach), you typically don't need to change anything. Your code will work as-is:

```php
<?php

// This still works in v2.0!
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('stripe', [
        'factory' => 'stripe_checkout',
        'publishable_key' => 'pk_test_...',
        'secret_key' => 'sk_test_...',
    ])
    ->getPayum();
```

### Optional: Use New DI Features

Take advantage of new DI features like global services:

```php
<?php

use Psr\Log\LoggerInterface;

$payum = (new PayumBuilder())
    ->addDefaultStorages()

    // NEW: Add global services
    ->addGlobalService(LoggerInterface::class, $logger)

    // NEW: Override default HTTP client
    ->addGlobalService(ClientInterface::class, $customHttpClient)

    ->addGateway('stripe', ['factory' => 'stripe_checkout', /* ... */])
    ->getPayum();
```

## For Custom Gateway Factory Authors

If you've created custom gateway factories, extend `CoreGatewayFactory` and move your configuration into
`configureContainer()`, `getActions()` and `getExtensions()`.

### Before (v1.x Pattern)

```php
<?php

namespace App\Payment;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class MyGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'my_gateway',
            'payum.factory_title' => 'My Gateway',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),

            'payum.api' => function (ArrayObject $config) {
                $config->validateNotEmpty(['client_id', 'client_secret']);

                return new Api(
                    $config['client_id'],
                    $config['client_secret'],
                    $config['sandbox'] ?? true
                );
            },
        ]);

        if (! $config['payum.api']) {
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty(['client_id', 'client_secret']);

                return new Api(
                    $config['client_id'],
                    $config['client_secret'],
                    $config['sandbox'] ?? true
                );
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumMyGateway' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
```

### After (v2.0 Pattern)

```php
<?php

namespace App\Payment;

use Payum\Core\CoreGatewayFactory;
use function DI\autowire;
use function DI\get;

class MyGatewayFactory extends CoreGatewayFactory
{
    public function configureContainer(): array
    {
        return array_merge(parent::configureContainer(), [
            // Configuration values
            'my_gateway.client_id' => '',
            'my_gateway.client_secret' => '',
            'my_gateway.sandbox' => true,

            // API class with dependency injection
            Api::class => autowire()
                ->constructor(
                    clientId: get('my_gateway.client_id'),
                    clientSecret: get('my_gateway.client_secret'),
                    sandbox: get('my_gateway.sandbox')
                ),

            // Actions with autowiring
            CaptureAction::class => autowire()
                ->constructorParameter('api', get(Api::class)),

            StatusAction::class => autowire()
                ->constructorParameter('api', get(Api::class)),

            // Template paths. Merge, do not replace, so that paths registered elsewhere survive.
            'payum.paths' => array_merge(
                parent::configureContainer()['payum.paths'] ?? [],
                [
                    'PayumMyGateway' => __DIR__ . '/Resources/views',
                ]
            ),
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

Two things worth noting:

- `CoreGatewayFactory` already implements `ContainerConfiguration`, so `extends CoreGatewayFactory` is
  enough — you do not have to repeat `implements ContainerConfiguration`.
- `populateConfig()` is replaced by `configureContainer()`. Drop it; there is nothing to keep for
  backwards compatibility.

### Registering the Factory

Register the factory under a name and reference that name from `addGateway()`:

```php
<?php

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGatewayFactory('my_gateway', new MyGatewayFactory())
    ->addGateway('mine', [
        'factory' => 'my_gateway',

        // These override the defaults declared in configureContainer()
        'my_gateway.client_id' => 'theClientId',
        'my_gateway.client_secret' => 'theClientSecret',
        'my_gateway.sandbox' => false,
    ])
    ->getPayum();
```

## Key Changes

### 1. Extend `CoreGatewayFactory` Instead of `GatewayFactory`

```php
// Before
class MyGatewayFactory extends GatewayFactory

// After — CoreGatewayFactory already implements ContainerConfiguration
class MyGatewayFactory extends CoreGatewayFactory
```

### 2. Implement `configureContainer()` Instead of `populateConfig()`

```php
// Before
protected function populateConfig(ArrayObject $config): void

// After
public function configureContainer(): array
```

### 3. Use `configureContainer()` Return Array, Not ArrayObject Mutation

```php
// Before
$config->defaults(['key' => 'value']);

// After
return array_merge(parent::configureContainer(), [
    'key' => 'value',
]);
```

### 4. Use Dependency Injection Instead of Closures

```php
// Before
'payum.api' => function (ArrayObject $config) {
    return new Api($config['client_id'], $config['client_secret']);
}

// After
Api::class => autowire()
    ->constructor(
        get('my_gateway.client_id'),
        get('my_gateway.client_secret')
    )
```

### 5. Return Your Actions From `getActions()`

```php
// Before
// Actions were automatically added from 'payum.action.*' keys

// After — return class names; createGateway() resolves and registers them for you
public function getActions(): array
{
    return array_merge(parent::getActions(), [
        CaptureAction::class,
        StatusAction::class,
    ]);
}
```

`getActions()` must return **class strings**, not instances — each one is resolved from the container so
that its dependencies get injected. `getExtensions()` works the same way, but also accepts ready-made
extension instances.

Actions are registered in the order returned. To have one consulted first regardless of position,
implement `Payum\Core\Action\PrependActionInterface` on it (or
`Payum\Core\Extension\PrependExtensionInterface` for an extension).

Override `createGateway()` only when you need to do something to the `Gateway` object itself that these two
methods cannot express.

## For Action Authors

### Before (ApiAwareInterface - Deprecated)

```php
<?php

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;

class CaptureAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function execute($request): void
    {
        $this->api->capturePayment(/* ... */);
    }
}
```

### After (Constructor Injection - Recommended)

```php
<?php

use Payum\Core\Action\ActionInterface;

class CaptureAction implements ActionInterface
{
    public function __construct(
        private readonly Api $api
    ) {}

    public function execute($request): void
    {
        $this->api->capturePayment(/* ... */);
    }

    public function supports($request): bool
    {
        return $request instanceof Capture;
    }
}
```

## Deprecated APIs

The following are deprecated and will be removed in v3.0. Everything still works until then, so you can
migrate at your own pace.

| Deprecated | Replacement |
|---|---|
| `GatewayFactory` as the base class for a gateway factory | `CoreGatewayFactory` |
| `GatewayFactory::populateConfig()` | `configureContainer()` |
| `GatewayFactoryInterface::createConfig()` | `configureContainer()` |
| `GatewayFactoryInterface::create()` | `createGateway()` |
| `'payum.action.*'` and `'payum.extension.*'` config keys | `getActions()` and `getExtensions()` |
| `'payum.api.*'` config keys and `ApiAwareInterface` | Constructor injection (see [For Action Authors](#for-action-authors)) |
| `'httplug.client'` | `Psr\Http\Client\ClientInterface` |
| `'httplug.stream_factory'` | `Psr\Http\Message\StreamFactoryInterface` |
| `'httplug.message_factory'` | `Psr\Http\Message\RequestFactoryInterface` |
| `Payum\Core\Bridge\Spl\ArrayObject` as a service | `Psr\Container\ContainerInterface` |

## Testing Your Migration

### 1. Run Existing Tests

```bash
bin/phpunit
```

Your existing tests should keep passing.

### 2. Test New Features

Add tests for new DI features:

```php
<?php

class PayumBuilderTest extends TestCase
{
    public function testGlobalServices(): void
    {
        $logger = new NullLogger();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(LoggerInterface::class, $logger)
            ->addGateway('offline', ['factory' => 'offline'])
            ->getPayum();

        // Verify gateway was created successfully
        $this->assertInstanceOf(GatewayInterface::class, $payum->getGateway('offline'));
    }
}
```

## Common Migration Issues

### Issue: "Service not found"

**Cause:** Service ID doesn't match

**Solution:** Use fully-qualified class names:

```php
// Wrong
get('Api')

// Correct
get(Api::class)
```

### Issue: "Circular dependency detected"

**Cause:** Services depend on each other

**Solution:** Use `get()` for lazy resolution:

```php
// Wrong - creates instance immediately
MyService::class => new MyService($container->get(OtherService::class))

// Correct - lazy resolution
MyService::class => autowire()
    ->constructor(get(OtherService::class))
```

### Issue: Actions not receiving API

**Cause:** Forgot to inject API in `configureContainer()`

**Solution:** Add constructor parameter in action definition:

```php
CaptureAction::class => autowire()
    ->constructorParameter('api', get(Api::class))
```

## Need Help?

- 📖 [DI Getting Started Guide](getting-started.md)
- 📖 [DI Customization Guide](customization.md)
- 🐛 [Report Issues](https://github.com/Payum/Payum/issues)
- 💬 [Community Discussions](https://github.com/Payum/Payum/discussions)

## Summary Checklist

For custom gateway factories:

- [ ] Extend `CoreGatewayFactory` instead of `GatewayFactory` (it implements `ContainerConfiguration` already)
- [ ] Move the config from `populateConfig()` into `configureContainer()`
- [ ] Return your action class names from `getActions()` (and extensions from `getExtensions()`)
- [ ] Use `autowire()` and `get()` for dependencies
- [ ] Drop `populateConfig()` — `configureContainer()` replaces it
- [ ] Update actions to use constructor injection
- [ ] Run tests and verify no errors
- [ ] Check for and address deprecation warnings

For PayumBuilder users:

- [ ] Optionally add global services with `addGlobalService()`
- [ ] Optionally override default services
- [ ] Run tests and verify everything works
- [ ] Consider using new DI features for better customization
