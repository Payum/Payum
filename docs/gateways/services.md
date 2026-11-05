# Services

Payum builds a [PHP-DI](https://php-di.org/) container for each registered gateway and autowires your
API class and handlers from it. Most gateways declare no services at all: a constructor that asks for
classes and interfaces Payum already knows is enough.

## How the containers fit

Each gateway's container sits in front of one global container that holds what every gateway shares:

```
global container      token storage, token issuer, request verifier, storages,
      ▲               PSR-18 client, PSR-17 factories, clock, event dispatcher
      │ fallback
gateway container     the config, the gateway, one entry per handler,
                      plus whatever the gateway declares
```

Anything the gateway container cannot resolve is looked up in the global one. So two gateways get the
same HTTP client but their own API objects, each built with its own config.

The gateway container is built in this order, and a later definition replaces an earlier one with the
same id:

1. Payum's own defaults.
2. The services shared by every gateway, including anything the application registered with
   `PayumBuilder::addGlobalService()`.
3. One entry per handler, autowired.
4. What the gateway declares in `configureContainer()`.
5. The config and the gateway instance.

## What you can take in a constructor

These are registered for you:

| Id | What it is |
| :--- | :--- |
| `AcmeConfig::class`, `Payum\Core\Config\GatewayConfig` | The config the application registered |
| `AcmeGateway::class`, `Payum\Core\Gateway\GatewayInterface` | The gateway instance |
| `CaptureHandlerInterface::class` and the other handler interfaces | The handler you listed, autowired |
| `Psr\Http\Client\ClientInterface` | The PSR-18 HTTP client, shared |
| `Psr\Http\Message\RequestFactoryInterface`, `StreamFactoryInterface` | PSR-17 factories, shared |
| `Psr\Http\Message\ServerRequestInterface` | The inbound HTTP request. In a handler, use `$context->httpRequest()` |
| `Psr\Clock\ClockInterface` | The current time, shared |
| `Psr\EventDispatcher\EventDispatcherInterface` | The application's PSR-14 dispatcher, or one that does nothing |
| `Money\Currencies`, `Money\MoneyFormatter`, `Money\MoneyParser` | Currency data and decimal conversion. See [Money and currencies](../money.md) |
| `Payum\Core\Security\TokenIssuerInterface` | Creates tokens. In a handler, use `$context->tokens()` |
| `Payum\Core\Registry\StorageRegistryInterface` | The application's storages |
| `Payum\Core\Template\RendererInterface` | Renders templates. Owned by the application, see [Templates](templates.md) |

The API class from [Build a gateway](../develop-gateway-with-payum.md) asks only for entries in this
table, so it needs no definition:

```php
final class AcmeApi
{
    public function __construct(
        private readonly AcmeConfig $config,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }
}
```

## Read the time from the clock

Take `Psr\Clock\ClockInterface` for anything that compares against the current time, such as an
authorization's expiry or a webhook's age, instead of calling `time()` or `new DateTimeImmutable()`:

```php
use Psr\Clock\ClockInterface;

final class SyncHandler implements SyncHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
        private readonly ClockInterface $clock,
    ) {
    }

    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        $checkout = $this->api->retrieveCheckout($context->state()['checkout_id']);

        if ('open' === $checkout['status'] && new DateTimeImmutable($checkout['expires_at']) < $this->clock->now()) {
            return SyncResult::synced(PaymentStatus::Expired, raw: $checkout);
        }

        // ...
    }
}
```

Every gateway shares one clock, so a test can fix the time for all of them with
`PayumBuilder::addGlobalService(ClockInterface::class, $clock)`.

## Declare a service

When autowiring cannot build a service, implement `Payum\Core\DI\ContainerConfiguration` on the
gateway and return PHP-DI definitions. Suppose Acme publishes an SDK whose client takes the API key as a
string. A string has no type to autowire, so define the client yourself:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Acme\Sdk\AcmeClient;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Gateway\GatewayInterface;
use Psr\Container\ContainerInterface;

final class AcmeGateway implements GatewayInterface, ContainerConfiguration
{
    public function configureContainer(): array
    {
        return [
            AcmeClient::class => static fn (ContainerInterface $c): AcmeClient => new AcmeClient(
                $c->get(AcmeConfig::class)->apiKey,
                sandbox: $c->get(AcmeConfig::class)->sandbox,
            ),
        ];
    }

    // name(), logo(), websiteUrl(), configClass() and handlers() as before
}
```

A class that takes `AcmeClient` in its constructor is then autowired as usual. Use
`configureContainer()` for a constructor argument that is a string, an int or an array, or an interface
with more than one implementation. If you need it for your own API class, make that class take the
config object instead.

## Replace a service for one gateway

Your definitions come after Payum's and the shared ones, so defining the same id replaces it for this
gateway only:

```php
public function configureContainer(): array
{
    return [
        ClientInterface::class => static fn (): ClientInterface => new AcmeRetryingClient(),
    ];
}
```

Other gateways keep the shared client.

## Decorate a handler

To wrap a handler, define the handler interface as a decorator around the handler class:

```php
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

public function configureContainer(): array
{
    return [
        CaptureHandlerInterface::class => static fn (ContainerInterface $c): CaptureHandlerInterface => new LoggingCaptureHandler(
            $c->get(CaptureHandler::class),
            $c->get(LoggerInterface::class),
        ),
    ];
}
```

The decorator implements the same interface, so Payum calls it in place of the handler, and
`CaptureHandler` is still autowired. The same works for building a handler yourself: define
`CaptureHandler::class` and Payum uses your definition.

`LoggerInterface` is not one of Payum's defaults, so this assumes the application registered a logger
with `addGlobalService()`. To log every command on every gateway, write a [middleware](../middleware.md)
instead.

## Two API versions

Give each version its own class. Each handler asks for the one it uses:

```php
final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly AcmeApiV2 $api,
    ) {
    }
}
```

## Services for every gateway

Services the application wants every gateway to use are registered on the builder, not in a gateway:

```php
$payum = (new PayumBuilder())
    ->addGlobalService(ClientInterface::class, $instrumentedClient)
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

See [Replace and add services](../di/customization.md) for the application's side.

## Events

Payum dispatches PSR-14 events for every command, such as `StatusChanged` when a result moves a
payment's status. A gateway does not dispatch them itself. See [Events](../events.md).

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
