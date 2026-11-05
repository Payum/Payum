# Integrate with a framework

Payum does not depend on a framework. In any application you build the `Payum` object once with
`PayumBuilder`, register it as a service, and inject it where you take payments. Your framework's own
services, such as its HTTP client, logger or event dispatcher, reach Payum through `addGlobalService()` or
`setGlobalContainer()`.

{% hint style="info" %}
PayumBundle for Symfony and the Laravel package have not been ported to Payum 2.x yet. Until they are,
wire Payum yourself as this page shows. See [Symfony](../symfony/README.md) and [Laravel](../laravel/README.md)
for the packages themselves.
{% endhint %}

## Build Payum once

Whatever the framework, put the builder in one factory and share its result. Building Payum creates its
containers, so do it once per process, not once per call:

```php
<?php

namespace App\Payment;

use Acme\Payum\AcmeConfig;
use App\Entity\Payment;
use App\Entity\PaymentToken;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final class PayumFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $acmeApiKey,
    ) {
    }

    public function create(): Payum
    {
        return (new PayumBuilder())
            ->setTokenStorage(new DoctrineStorage($this->entityManager, PaymentToken::class))
            ->addStorage(Payment::class, new DoctrineStorage($this->entityManager, Payment::class))
            ->addGlobalService(ClientInterface::class, $this->httpClient)
            ->addGlobalService(LoggerInterface::class, $this->logger)
            ->addGlobalService(EventDispatcherInterface::class, $this->eventDispatcher)
            ->registerGateway('acme', new AcmeConfig(apiKey: $this->acmeApiKey, sandbox: true))
            ->getPayum();
    }
}
```

The factory takes the framework's services as constructor arguments and hands them to Payum. Keep
credentials out of the code and pass them in from your framework's environment configuration.

## Symfony

Register the factory, then register `Payum\Core\Payum` as a service it creates:

```yaml
# config/services.yaml
services:
    App\Payment\PayumFactory:
        arguments:
            $acmeApiKey: '%env(ACME_API_KEY)%'

    Payum\Core\Payum:
        factory: ['@App\Payment\PayumFactory', 'create']
```

With autowiring on, Symfony passes the entity manager, its PSR-18 client, its logger and its event
dispatcher to the factory. Its `event_dispatcher` already implements PSR-14. The PSR-18 client needs
`symfony/http-client`. Inject `Payum` into a controller like any other service.

## Laravel

Bind `Payum` as a singleton in a service provider:

```php
<?php

namespace App\Providers;

use Acme\Payum\AcmeConfig;
use Illuminate\Support\ServiceProvider;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Psr\Log\LoggerInterface;

final class PayumServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Payum::class, static fn ($app): Payum => (new PayumBuilder())
            ->addGlobalService(LoggerInterface::class, $app->make(LoggerInterface::class))
            ->registerGateway('acme', new AcmeConfig(apiKey: config('services.acme.key'), sandbox: true))
            ->getPayum());
    }
}
```

Add a token storage and a storage for your payment model too. Payum ships no Eloquent storage, so either
use Doctrine or write your own storage, as [Storages](../storages.md#write-your-own-storage) shows.

## Plain PHP

Without a framework, build Payum in a file that every script includes:

```php
<?php
// config.php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

require __DIR__ . '/vendor/autoload.php';

$payum = (new PayumBuilder())
    ->registerGateway('acme', new AcmeConfig(apiKey: getenv('ACME_API_KEY'), sandbox: true))
    ->getPayum();
```

[Get started](../get-it-started.md) builds the scripts that include it.

## Use your framework's container

Instead of passing services one by one, you can give Payum your application's container with
`setGlobalContainer()`. Payum asks it first for every service it needs, and uses its own default for any id
the container does not have. So you only define the services you want to replace:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */

$payum = (new PayumBuilder())
    ->setGlobalContainer($container)
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Any PSR-11 container works. Payum looks up its services by these ids, so define one of them in your
container to replace Payum's default:

| Id | Replaces |
| --- | --- |
| `payum.security.token_storage` | The token storage. When your container has it, Payum creates none of its default storages. |
| `Payum\Core\Security\TokenFactoryInterface` | The factory that stores tokens and builds their URLs. |
| `Payum\Core\Security\TokenIssuerInterface` | The service that issues tokens. |
| `Payum\Core\Security\HttpRequestVerifierInterface` | The service that finds the token in a request. |
| `twig.env` or `Twig\Environment` | The Twig environment payment pages render with. |
| `Psr\Http\Client\ClientInterface` and the other shared services | See [Configure Payum](getting-started.md#services-shared-by-every-gateway). |

A token storage set with `setTokenStorage()` wins over one in your container.

### Let gateways use your container's services

PSR-11 has no way to list what a container holds. Payum needs that list to pass your services into a
gateway's constructors, so a service that only your container knows reaches a gateway only when Payum can
list it. A PHP-DI container can, and so can a container that implements
`Payum\Core\DI\ListableContainerInterface`.

For a container that cannot list its services, also register the services a gateway needs with
`addGlobalService()`:

```php
<?php

use Psr\Log\LoggerInterface;

$builder
    ->setGlobalContainer($container)
    ->addGlobalService(LoggerInterface::class, static fn () => $container->get(LoggerInterface::class));
```

In Symfony, a service locator narrows what Payum sees and can list it. Wrap one in a class that implements
`ListableContainerInterface`:

```php
<?php

namespace App\Payment;

use Payum\Core\DI\ListableContainerInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

final class PayumContainer implements ListableContainerInterface
{
    public function __construct(
        private readonly ServiceProviderInterface $locator,
    ) {
    }

    public function get(string $id): mixed
    {
        return $this->locator->get($id);
    }

    public function has(string $id): bool
    {
        return $this->locator->has($id);
    }

    public function getKnownEntryNames(): array
    {
        return array_keys($this->locator->getProvidedServices());
    }
}
```

Then give it the services Payum should use:

```yaml
# config/services.yaml
services:
    App\Payment\PayumContainer:
        arguments:
            $locator: !service_locator
                Psr\Log\LoggerInterface: '@logger'
                Psr\EventDispatcher\EventDispatcherInterface: '@event_dispatcher'
                Twig\Environment: '@twig'
```

Pass `App\Payment\PayumContainer` to `setGlobalContainer()` in your factory. Every service in the locator
now replaces Payum's default and is available to every gateway's constructors.

## Advanced: long-running workers

In a worker that serves many requests, such as RoadRunner, Swoole or FrankenPHP, there is no single
current request in PHP's globals. Two services depend on it:

- The incoming PSR-7 request. Register the request being served as `Psr\Http\Message\ServerRequestInterface`,
  as [Replace and add services](customization.md#the-incoming-request) shows.
- The token factory reads the host from `$_SERVER` unless you give it a base URL. Set one with
  `setTokenFactory()`, as [Configure Payum](getting-started.md#configure-payment-urls) shows.

Payum resolves each service once per container, so a request registered as a fixed instance stays the same
for the life of the `Payum` object. Build Payum per request in a worker if your gateways read the request.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
