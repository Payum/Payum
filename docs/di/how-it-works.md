# How dependency injection works

Payum builds its services, and every gateway's handlers and API classes, with a dependency injection
container. You rarely talk to the container directly: you give services to the builder, and gateways
receive them through their constructors. Read this page when you replace one of Payum's services, want a
gateway to use a service of yours, or get an error saying an entry "cannot be resolved".

Payum's containers are [PHP-DI](https://php-di.org/) containers. You do not need to use PHP-DI in your
application, and your own container can be any PSR-11 container.

## One global container, one container per gateway

Payum keeps one global container for the services every gateway shares, and builds one container for each
gateway you register:

```
                ┌───────────────────────────────────────────┐
                │ Your container (setGlobalContainer())     │  asked first, optional
                └─────────────────────┬─────────────────────┘
                                      │ ids it does not have
                ┌─────────────────────▼─────────────────────┐
                │ Global container                          │
                │ token storage, token issuer, verifier,    │
                │ HTTP client, PSR-17 factories, clock,     │
                │ event dispatcher, renderer,               │
                │ everything from addGlobalService()        │
                └─────────────────────┬─────────────────────┘
                                      │ shared with every gateway
           ┌──────────────────────────┼──────────────────────────┐
┌──────────▼──────────┐    ┌──────────▼──────────┐    ┌──────────▼──────────┐
│ 'acme' container    │    │ 'acme_eu' container │    │ 'other' container   │
│ its config, API,    │    │ its config, API,    │    │ its config, API,    │
│ handlers, request   │    │ handlers, request   │    │ handlers, request   │
└─────────────────────┘    └─────────────────────┘    └─────────────────────┘
```

A service in the global container is created once, the first time something needs it, and every gateway
gets that same instance. A service in a gateway container belongs to that gateway only. Two gateways
registered from the same gateway class with different credentials get separate configs, API clients and
handlers, but share one HTTP client.

## Services shared by every gateway

These are in the global container by default:

| Service id | Default | What it is |
| --- | --- | --- |
| `payum.security.token_storage` | The token storage you set | Where tokens are stored. |
| `Payum\Core\Security\TokenIssuerInterface` | Built from the token factory and the paths | Issues a token for a purpose (capture, refund, notify, ...). |
| `Payum\Core\Security\TokenFactoryInterface` | See `setTokenFactory()` | Stores a token and builds its URL. |
| `Payum\Core\Security\HttpRequestVerifierInterface` | See `setHttpRequestVerifier()` | Finds and checks the token in an incoming request. |
| `Payum\Core\Registry\StorageRegistryInterface` | Your storages | Finds the storage for a model class. |
| `Psr\Http\Client\ClientInterface` | Found by `php-http/discovery` | The PSR-18 HTTP client gateways call their PSP with. |
| `Psr\Http\Message\RequestFactoryInterface` | Found by `php-http/discovery` | PSR-17 factory for outgoing requests. |
| `Psr\Http\Message\StreamFactoryInterface` | Found by `php-http/discovery` | PSR-17 factory for request bodies. |
| `Psr\Clock\ClockInterface` | `Payum\Core\Clock\SystemClock` | The current time, for anything that expires. |
| `Psr\EventDispatcher\EventDispatcherInterface` | `Payum\Core\Event\NullEventDispatcher`, which drops every event | Receives the payment lifecycle [events](../events.md). |
| `Payum\Core\Template\RendererInterface` | `Payum\Core\Bridge\Twig\TwigRenderer` | Renders the payment pages a gateway asks for. |
| Any id you pass to `addGlobalService()` | | Shared like the rest. |

## Services each gateway gets

Each gateway container holds:

- The gateway's config object, registered under its own class (such as `Acme\Payum\AcmeConfig`) and under
  `Payum\Core\Config\GatewayConfig`.
- The gateway's handlers, its API class and any services the gateway declares for itself.
- Two defaults that are not shared:

| Service id | Default | What it is |
| --- | --- | --- |
| `Psr\Http\Message\ServerRequestInterface` | Built from PHP's globals and `php://input` | The incoming request, for redirects back from the PSP and webhooks. |
| `Money\Currencies` | ISO 4217 plus moneyphp's crypto currencies | How many decimal places each currency has. See [Money and currencies](../money.md). |

Register either one with `addGlobalService()` and every gateway uses yours instead.

## How a service reaches a gateway

The gateway container builds handlers, API classes and middleware by autowiring: it reads the types in
their constructors and passes the service registered under each type. A gateway's API class, for example,
asks for its config and the HTTP client:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class AcmeApi
{
    public function __construct(
        private readonly AcmeConfig $config,
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }
}
```

The config comes from the gateway's own container, and the client and factories from the global one.
Nobody registers `AcmeApi`: the container builds it from its constructor.

Because services are matched by type, register yours under the class or interface a constructor asks for:

```php
use Psr\Log\LoggerInterface;

$builder->addGlobalService(LoggerInterface::class, $logger);
```

A logger registered as `'logger'` is never passed to a constructor that asks for a `LoggerInterface`.

A class nobody registered is still autowired, but separately in each gateway container. If a service must
be one instance for the whole application, such as a connection pool or an in-memory cache, register it
with `addGlobalService()`.

## Register a service

`addGlobalService()` takes the service id and one of three things.

An object you have already built:

```php
$builder->addGlobalService(LoggerInterface::class, $logger);
```

A closure, which Payum calls the first time the service is needed and only once:

```php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

$builder->addGlobalService(LoggerInterface::class, static function (): LoggerInterface {
    $logger = new Logger('payum');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/var/log/payum.log'));

    return $logger;
});
```

Use a closure for anything expensive to create, so a request that never touches it does not pay for it. A
closure can ask for the container, to build a service from other services:

```php
use App\Fraud\FraudChecker;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;

$builder->addGlobalService(
    FraudChecker::class,
    static fn (ContainerInterface $container): FraudChecker => new FraudChecker($container->get(ClockInterface::class)),
);
```

Or a PHP-DI definition, when you already use PHP-DI and want it to autowire the class:

```php
use App\Fraud\FraudChecker;

use function DI\autowire;

$builder->addGlobalService(FraudChecker::class, autowire());
```

All three are shared: every gateway gets the same instance. [Replace and add services](customization.md)
has a recipe for each service Payum uses.

## Which definition wins

When the same id is defined in more than one place, the gateway container uses the last of these:

1. Payum's per-gateway defaults, such as `ServerRequestInterface` and `Money\Currencies`.
2. The shared services, including everything from `addGlobalService()` and `setGlobalContainer()`.
3. The gateway's handlers.
4. The services the gateway declares for itself.
5. The gateway's config object.

So a service you register replaces Payum's default in every gateway, but not a service a gateway declares
for itself. When a gateway container has no entry for an id, it asks the global container. A
`Payum\Core\DI\FallbackContainer` does that lookup.

A gateway cannot declare its own `Payum\Core\Template\RendererInterface`, because one renderer serves
every gateway. `getPayum()` throws a `LogicException` if one tries.

## Use your own container

If your framework already has a container, give it to Payum with `setGlobalContainer()`. Payum asks your
container first for every id, and uses `addGlobalService()` or its own default only for an id your
container does not have. So you only register what you want to replace.

Gateways see the services in your container only if Payum can list them: a PHP-DI container, or one that
implements `Payum\Core\DI\ListableContainerInterface`. For any other container, also register what a
gateway needs with `addGlobalService()`. [Integrate with a framework](framework-integration.md) shows both
for Symfony and Laravel.

## Troubleshooting

### An entry cannot be resolved

```
Entry "App\Payment\AuditMiddleware" cannot be resolved: Entry "App\Audit\AuditLogInterface" cannot be resolved: the class is not instantiable
```

A constructor asks for an interface that nothing registered, so the container cannot build it. Register a
service under that exact interface with `addGlobalService()`, or in your own container. Check that the id
is the interface's full class name and not a string alias.

### Circular dependency detected

```
Circular dependency detected while trying to resolve entry 'App\A': Dependencies: App\Payment\AuditMiddleware -> App\A -> App\B -> App\A
```

Two classes ask for each other in their constructors. The message lists the chain. Break it by moving the
shared part into a third class both can depend on.

### A service is created more than once

A class you did not register is autowired in each gateway container, so each gateway builds its own. If
you need a single instance, register it with `addGlobalService()`.

### Your service is ignored

A gateway that declares a service for itself keeps its own, whatever you register. Check the gateway's
documentation for how it lets you replace it. If you use `setGlobalContainer()` with a container Payum
cannot list, a gateway does not see your container's services: register them with `addGlobalService()`
too.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
