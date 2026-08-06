# Dependency Injection - Customization Guide

## Overview

Payum v2.0's DI system is designed for maximum flexibility. You can customize virtually any service, add your own global services, or integrate with external containers.

## Adding Global Services

Global services are available to all gateways and are instantiated only once. Every id you register with
`addGlobalService()` is wired into each gateway's container, so gateway factories and actions can ask for
it by that id.

### Adding a Logger

```php
<?php

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('payum');
$logger->pushHandler(new StreamHandler('/var/log/payum.log'));

$payum = (new PayumBuilder())
    ->addGlobalService(LoggerInterface::class, $logger)
    ->addGlobalService('my.custom.logger', $logger) // Alternative ID
    ->addDefaultStorages()
    // ... add gateways
    ->getPayum();
```

### Adding a Cache Service

```php
<?php

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

$redisClient = new \Redis();
$redisClient->connect('127.0.0.1', 6379);

$cache = new Psr16Cache(new RedisAdapter($redisClient));

$payum = (new PayumBuilder())
    ->addGlobalService(CacheInterface::class, $cache)
    ->addDefaultStorages()
    // ... add gateways
    ->getPayum();
```

## Overriding Default Services

### Custom HTTP Client

Replace the default PSR-18 HTTP client with your own:

```php
<?php

use Psr\Http\Client\ClientInterface;
use GuzzleHttp\Client;

$customClient = new Client([
    'timeout' => 30,
    'verify' => true,
    'headers' => [
        'User-Agent' => 'MyApp/1.0',
    ],
]);

$payum = (new PayumBuilder())
    ->addGlobalService(ClientInterface::class, $customClient)
    ->addDefaultStorages()
    // ... add gateways
    ->getPayum();
```

### Custom HTTP Factory (PSR-17)

```php
<?php

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

$factory = new Psr17Factory();

$payum = (new PayumBuilder())
    ->addGlobalService(RequestFactoryInterface::class, $factory)
    ->addGlobalService(StreamFactoryInterface::class, $factory)
    ->addDefaultStorages()
    // ... add gateways
    ->getPayum();
```

### Custom Token Storage

```php
<?php

use Payum\Core\Storage\StorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;

$tokenStorage = new DoctrineStorage(
    $entityManager,
    'App\Entity\PaymentToken'
);

$payum = (new PayumBuilder())
    ->setTokenStorage($tokenStorage)
    // ... add gateways
    ->getPayum();
```

## Using Service Factories

For services that need lazy initialization or configuration:

```php
<?php

use Psr\Log\LoggerInterface;

$payum = (new PayumBuilder())
    ->addGlobalService(LoggerInterface::class, function () {
        // This closure is called only when the service is first requested
        $logger = new \Monolog\Logger('payum');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler('/var/log/payum.log'));
        return $logger;
    })
    ->addDefaultStorages()
    // ... add gateways
    ->getPayum();
```

## Gateway-Specific Configuration

You can add services specific to a single gateway:

```php
<?php

$payum = (new PayumBuilder())
    ->addDefaultStorages()

    ->addGateway('stripe', [
        'factory' => 'stripe_checkout',
        'publishable_key' => 'pk_test_...',
        'secret_key' => 'sk_test_...',

        // Gateway-specific service (not shared)
        'stripe.custom_option' => 'custom_value',
    ])

    ->getPayum();
```

## Advanced: Using Your Own Container

For framework integration you can hand your application's container to `setGlobalContainer()`. It is placed
**in front of** Payum's global container: a service is looked up in your container first, and whatever it
does not have comes from Payum's defaults.

That means you only declare the services you actually want to provide yourself. There is no need to
re-create the token factory, the request verifier or the HTTP client just to get a container accepted.

```php
<?php

use DI\ContainerBuilder;
use Psr\Http\Client\ClientInterface;

// Only what you want to control yourself
$builder = new ContainerBuilder();
$builder->addDefinitions([
    ClientInterface::class => function () {
        return new \GuzzleHttp\Client(['timeout' => 60]);
    },
    'my.custom.service' => function () {
        return new MyCustomService();
    },
]);

$payum = (new PayumBuilder())
    ->setGlobalContainer($builder->build())
    // ... add gateways
    ->getPayum();
```

Here the gateways use your Guzzle client, while the token storage, token factories and request verifier are
still the ones Payum builds. Override any of them by adding a definition for
`payum.security.token_storage`, `TokenFactoryInterface`, `GenericTokenFactoryInterface` or
`HttpRequestVerifierInterface` to your container — Payum picks them up and builds the rest on top of them.
Define the token storage there and the default storages are not created at all.

`addGlobalService()` keeps working alongside a container of your own; those services are layered under it.

### Reaching Your Services From a Gateway

Pass a PHP-DI container and everything in it — including `'my.custom.service'` — can be injected into your
gateway's actions.

Most framework containers cannot list what they hold, so Payum has no way to know which ids to make
injectable. Register those with `addGlobalService()`, which works alongside your container:

```php
<?php

$payum = (new PayumBuilder())
    ->setGlobalContainer($frameworkContainer)

    // Makes the logger injectable into gateway actions
    ->addGlobalService(LoggerInterface::class, fn () => $frameworkContainer->get('logger'))

    // ... add gateways
    ->getPayum();
```

## Accessing Services

### From Gateway Context

If you've added a global service, you can access it from within actions:

```php
<?php

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\Capture;
use Psr\Log\LoggerInterface;

class MyAction implements ActionInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function execute($request): void
    {
        $this->logger->info('Processing payment request');
        // ... action logic
    }

    public function supports($request): bool
    {
        return $request instanceof Capture;
    }
}
```

### Registering Actions with Dependencies

When creating gateway factories, inject dependencies via constructor:

```php
<?php

use Payum\Core\CoreGatewayFactory;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\get;

class MyGatewayFactory extends CoreGatewayFactory
{
    public function configureContainer(): array
    {
        return array_merge(parent::configureContainer(), [
            MyAction::class => autowire()
                ->constructor(logger: get(LoggerInterface::class)),
        ]);
    }

    public function getActions(): array
    {
        return array_merge(parent::getActions(), [
            MyAction::class,
        ]);
    }
}
```

`get(LoggerInterface::class)` resolves here because the logger was registered with
`addGlobalService(LoggerInterface::class, $logger)` — shared services are wired into every gateway
container.

## Service Lifecycle

### Global Services (Singleton)

Services in the global container are instantiated once and reused:

```php
<?php

$payum = (new PayumBuilder())
    ->addGlobalService(ClientInterface::class, $httpClient)
    ->addGateway('stripe', ['factory' => 'stripe_checkout', /* ... */])
    ->addGateway('paypal', ['factory' => 'paypal_rest', /* ... */])
    ->getPayum();

// Both gateways will use the SAME $httpClient instance
$stripe = $payum->getGateway('stripe');
$paypal = $payum->getGateway('paypal');
```

### Per-Gateway Services (Per-Gateway Instance)

Services defined in gateway-specific config are instantiated per gateway:

```php
<?php

$payum = (new PayumBuilder())
    ->addGateway('stripe1', [
        'factory' => 'stripe_checkout',
        'secret_key' => 'sk_test_account1',
        // Each gateway has its own API client
    ])
    ->addGateway('stripe2', [
        'factory' => 'stripe_checkout',
        'secret_key' => 'sk_test_account2',
        // Separate API client instance
    ])
    ->getPayum();
```

## Best Practices

### 1. Use Type Hints

Always use class names or interface names as service IDs:

```php
// ✅ Good
->addGlobalService(LoggerInterface::class, $logger)

// ❌ Avoid
->addGlobalService('logger', $logger)
```

### 2. Prefer Interfaces Over Concrete Classes

Use PSR interfaces when available:

```php
// ✅ Good - uses PSR-18
->addGlobalService(ClientInterface::class, $client)

// ❌ Avoid - couples to Guzzle
->addGlobalService(GuzzleClient::class, $client)
```

### 3. Use Lazy Loading for Expensive Services

Use closures for services that are expensive to create:

```php
// ✅ Good - lazy loaded
->addGlobalService(ClientInterface::class, function () {
    return new ExpensiveHttpClient();
})

// ❌ Avoid - created immediately even if never used
->addGlobalService(ClientInterface::class, new ExpensiveHttpClient())
```

### 4. Document Custom Services

When adding custom services, document them for your team:

```php
<?php

// Custom services:
// - AppLoggerInterface: Application logger with custom formatters
// - AppCacheInterface: Redis-backed cache for API responses
$payum = (new PayumBuilder())
    ->addGlobalService(AppLoggerInterface::class, $logger)
    ->addGlobalService(AppCacheInterface::class, $cache)
    // ...
```

## Troubleshooting

### Service Not Found

If you get a "service not found" error:

1. Check the service ID matches exactly (including namespace)
2. Ensure you added the service before calling `getPayum()`
3. Verify you're using the global container for shared services

### Service Not Shared

If services aren't being shared across gateways:

1. Use `addGlobalService()` not gateway-specific config
2. Check you're using the same service ID in both places

### Circular Dependencies

If you encounter circular dependency errors:

1. Use `get()` references instead of direct instantiation
2. Consider using setter injection for optional dependencies
3. Refactor to break the circular dependency

## See Also

- [Dependency Injection overview](README.md)
- [Getting Started](getting-started.md)
- [Migration Guide](migration-guide.md)
- [Framework Integration](framework-integration.md)
- [Storages](../storages.md)
- [The Architecture](../the-architecture.md)

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/Payum)
