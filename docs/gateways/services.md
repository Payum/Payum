# Services

Payum builds a [PHP-DI](https://php-di.org/) container per gateway, layered over one global container holding what every gateway shares.

```
global container          token storage, token factories, request verifier,
      ▲                   PSR-18 client, PSR-17 factories, storage registry
      │  fallback
gateway container         the config, the gateway, one binding per handler,
                          plus whatever the gateway declares
```

Anything the gateway container cannot resolve is looked up globally, so a gateway sees the shared services without redeclaring them, and two gateways get the same HTTP client but their own api objects.

Definitions are layered last-wins:

1. Payum's own defaults
2. the services shared by every gateway
3. whatever the gateway declares in `configureContainer()`
4. the config, the gateway instance, and one binding per handler

### What you get for free

For a typical gateway, **nothing needs declaring**. These are registered for you:

| Entry | What it is |
| :--- | :--- |
| `AcmeConfig::class` and `GatewayConfig::class` | The config you registered |
| `AcmeGateway::class` and `Gateway\GatewayInterface::class` | The gateway |
| `CaptureHandlerInterface::class` → `CaptureHandler` | One per handler, autowired |
| `ClientInterface::class` | PSR-18 client |
| `RequestFactoryInterface::class`, `StreamFactoryInterface::class` | PSR-17 factories |
| `ServerRequestInterface::class` | The inbound request, PSR-7 |
| `GenericTokenFactoryInterface::class`, `TokenFactoryInterface::class` | Token factories |
| `HttpRequestVerifierInterface::class` | Token verification |
| `StorageRegistryInterface::class` | Storages, for loading a payment from a token |

An api whose constructor takes only container entries is autowired with no definition:

```php
final class AcmeApi
{
    public function __construct(
        private readonly AcmeConfig $config,          // registered by Payum
        private readonly ClientInterface $httpClient, // shared globally
    ) {}
}
```

### Declaring a service

When autowiring cannot reach something, implement `ContainerConfiguration` on the gateway:

```php
<?php
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Gateway\GatewayInterface;
use Psr\Container\ContainerInterface;

final class AcmeGateway implements GatewayInterface, ContainerConfiguration
{
    public function configureContainer(): array
    {
        return [
            // An api taking an array cannot be autowired: an array has no type to resolve.
            AcmeApi::class => static fn (ContainerInterface $c): AcmeApi => new AcmeApi(
                $c->get(AcmeConfig::class)->toArray(),
                $c->get(ClientInterface::class),
            ),
        ];
    }
}
```

Reach for it when a service needs an array or a scalar, when the gateway ships two api versions, or when a definition depends on a runtime condition. Needing it for the api itself usually means the api should take the config object instead.

### Overriding a service

Definitions from `configureContainer()` win over Payum's defaults, so overriding is a matter of declaring the same id:

```php
public function configureContainer(): array
{
    return [
        // A client with this gateway's own timeout, only for this gateway.
        ClientInterface::class => static fn (): ClientInterface => new AcmeHttpClient(timeout: 5),
    ];
}
```

### Decorating a handler

Bind the handler interface to something that wraps the real one:

```php
use function DI\autowire;

public function configureContainer(): array
{
    return [
        CaptureHandler::class => autowire(),
        CaptureHandlerInterface::class => static fn (ContainerInterface $c): CaptureHandlerInterface
            => new LoggingCaptureHandler($c->get(CaptureHandler::class), $c->get(LoggerInterface::class)),
    ];
}
```

The decorator implements the same handler interface, so nothing else changes.

### Two api versions

Two classes, two entries. No `ApiAwareInterface`, no `UnsupportedApiException` — the handler asks for the one it wants:

```php
final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(private readonly AcmeApiV2 $api) {}
}
```

### Overriding from the application

Whole-application defaults go on the builder rather than the gateway, and reach every gateway at once:

```php
$payum = (new PayumBuilder())
    ->addGlobalService(ClientInterface::class, new MyInstrumentedHttpClient())
    ->registerGateway('acme', new AcmeConfig(…))
    ->getPayum();
```

`setGlobalContainer()` puts an application's own container in front of Payum's defaults, so a framework declares only what it wants to override. See [Framework integration](../di/framework-integration.md).

Next: [Migrating a gateway from 1.x](migrating-from-v1.md).
