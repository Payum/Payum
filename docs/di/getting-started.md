# Configure Payum

Your application talks to Payum through one `Payum\Core\Payum` object. You build it with
`Payum\Core\PayumBuilder`: register your gateways (the classes that talk to a payment service provider,
or PSP), tell Payum where to store payments and tokens, and replace any service you want to provide
yourself. This page lists every builder method and the services Payum shares between gateways.

## Build Payum

Create the builder, register a gateway and call `getPayum()`:

```php
<?php
// config.php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();

$gateway = $payum->getGateway('acme');
```

`registerGateway()` takes the name you use to look the gateway up later, and the gateway's config object.
The config class names its gateway, so Payum knows which gateway to build. If the config does not belong to
that gateway, `registerGateway()` throws a `LogicException` that names both classes.

This sample runs as it is, because Payum falls back to defaults for everything else. The default
storages write to the system temp directory, which is fine for trying Payum out but not for production.
The next section replaces them.

## Store payments and tokens

Payum stores two kinds of things: your payments, and the tokens behind the URLs it sends a customer to (a
token is a random hash that points at one payment). Give it a storage for each:

```php
<?php
// config.php

use Acme\Entity\Payment;
use Acme\Entity\PaymentToken;
use Acme\Payum\AcmeConfig;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\PayumBuilder;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$payum = (new PayumBuilder())
    ->setTokenStorage(new DoctrineStorage($entityManager, PaymentToken::class))
    ->addStorage(Payment::class, new DoctrineStorage($entityManager, Payment::class))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

`addStorage()` registers a storage for one model class. Payum looks the storage up by the exact class of
the model, so register it under your own class (`Acme\Entity\Payment`), not a parent class. Call it once
for each model class you pay with.

When you set no token storage, Payum calls `addDefaultStorages()` for you. It registers a
`FilesystemStorage` in the system temp directory for tokens and for `Payum\Core\Model\Payment`, `Payout`
and `ArrayObject`.

{% hint style="warning" %}
Do not run production on the default storages. The system may clear its temp directory at any time, and
`FilesystemStorage` does no locking. Set a token storage and a storage for your payment class.
{% endhint %}

[Storages](../storages.md) lists every storage Payum ships, shows the Doctrine setup and explains how to
write your own.

## Configure payment URLs

Payum sends the customer to a URL with a token in it, for example `https://shop.example.com/capture.php?payum_token=...`.
By default it builds these URLs from the host of the current request and these script names:

| Path | Default | Used for |
| --- | --- | --- |
| `capture` | `capture.php` | Taking a payment |
| `authorize` | `authorize.php` | Reserving an amount without taking it |
| `refund` | `refund.php` | Refunding a payment |
| `payout` | `payout.php` | Sending money to someone |
| `notify` | `notify.php` | Webhooks the PSP sends |
| `done` | `done.php` | Where the customer lands after a capture |

Change the paths with `setGenericTokenFactoryPaths()`, and set the base URL with `setTokenFactory()`:

```php
<?php
// config.php

use Acme\Payum\AcmeConfig;
use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->setTokenFactory(static fn ($tokenStorage, $storageRegistry) => new TokenFactory(
        $tokenStorage,
        $storageRegistry,
        'https://shop.example.com/payment',
    ))
    ->setGenericTokenFactoryPaths([
        'capture' => 'pay',
        'done' => 'thank-you',
    ])
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

With this configuration a capture URL is `https://shop.example.com/payment/pay?payum_token=...`. The paths
you pass replace only the defaults with the same key. Set the base URL whenever Payum issues tokens outside
a web request, such as in a console command or a queue worker. Without it, the default token factory reads
the host from `$_SERVER` and throws when there is none.

[Tokens and payment URLs](../tokens.md) explains how tokens are issued, verified and invalidated.

## Builder methods

Every method returns the builder, so you can chain them. Call `getPayum()` last.

| Method | Default | What it changes |
| --- | --- | --- |
| `registerGateway(string $name, GatewayConfig $config)` | No gateways | Registers a gateway under a name, from its config object. |
| `addStorage(string $modelClass, StorageInterface $storage)` | The default storages | Stores models of that class in that storage. |
| `setTokenStorage(?StorageInterface $tokenStorage)` | The default storages | Stores tokens in that storage. |
| `addDefaultStorages()` | Called when no token storage is set | Registers `FilesystemStorage` in the system temp directory for tokens, `Payment`, `Payout` and `ArrayObject`. |
| `setGenericTokenFactoryPaths(array $paths)` | See the table above | Changes the path of each kind of payment URL. |
| `setTokenFactory(TokenFactoryInterface\|callable\|null $tokenFactory)` | `Payum\Core\Bridge\PlainPhp\Security\TokenFactory`, with the host from `$_SERVER` | Replaces the factory that stores tokens and builds their URLs. A callable receives the token storage and the storage registry. |
| `setHttpRequestVerifier(HttpRequestVerifierInterface\|callable\|null $verifier)` | `Payum\Core\Bridge\PlainPhp\Security\HttpRequestVerifier` | Replaces the service that finds the token in an incoming request. A callable receives the token storage. |
| `addGlobalService(string $id, mixed $service)` | Nothing | Adds or replaces a service shared by every gateway. |
| `setGlobalContainer(ContainerInterface $container)` | None | Puts your own PSR-11 container in front of Payum's services. |
| `addMiddleware(string\|MiddlewareInterface $middleware, ?int $priority = null)` | Payum's own middleware | Wraps every command on every gateway. |
| `setLayout(string $layout)` | `@PayumCore/layout.html.twig` | Sets the Twig layout that every payment page extends. |
| `setMainRegistry(?RegistryInterface $registry)` | None | Adds a registry that Payum asks for gateways and storages before its own. |
| `getPayum()` | | Builds the `Payum` object. |

`addGlobalService()` and `setGlobalContainer()` are covered in [Replace and add services](customization.md).
`addMiddleware()` is covered in [Middleware](../gateways/middleware.md), and `setLayout()` in
[Customize payment pages](../payment-pages.md).

The builder also has methods for gateways built by 1.x gateway factories: `addGateway()` (deprecated),
`addGatewayFactory()`, `addGatewayFactoryConfig()`, `setCoreGatewayFactory()`, `setCoreGatewayFactoryConfig()`,
`addCoreGatewayFactoryConfig()` and `setGatewayConfigStorage()`. A gateway registered with
`registerGateway()` does not use them. If you are upgrading an application, see
[Upgrade from 1.x](../upgrading.md).

## Services shared by every gateway

Payum keeps one global container with the services every gateway uses. Each of these is created once and
shared, so two gateways get the same instance:

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

Each gateway also gets defaults of its own, which are not shared until you register a replacement with
`addGlobalService()`:

| Service id | Default | What it is |
| --- | --- | --- |
| `Psr\Http\Message\ServerRequestInterface` | Built from PHP's globals and `php://input` | The incoming request, for redirects back from the PSP and webhooks. |
| `Money\Currencies` | ISO 4217 plus moneyphp's crypto currencies | How many decimal places each currency has. See [Money and currencies](../money.md). |

[Replace and add services](customization.md) shows how to replace each of these.

## How the containers fit together

Payum uses [PHP-DI](https://php-di.org/) for its containers, but you only need to know this if you write
service definitions yourself. Payum builds one global container, and one container per gateway.

The global container holds the shared services above. When you call `setGlobalContainer()`, Payum puts your
container in front of it. Payum asks your container first and uses its own service only for an id your
container does not have.

Each gateway gets its own container, built from these layers. A later layer replaces an id an earlier one
defined:

1. Payum's per-gateway defaults, such as `ServerRequestInterface` and `Money\Currencies`.
2. The shared services from the global container.
3. The gateway's handlers (the classes that run each payment operation).
4. The services the gateway declares for itself.
5. The gateway's config object, registered under its own class and under `Payum\Core\Config\GatewayConfig`.

A service you register with `addGlobalService()` therefore replaces Payum's default in every gateway,
unless a gateway declares that service for itself. When a gateway container has no entry for an id, it
falls back to the global container. A `Payum\Core\DI\FallbackContainer` does this lookup.

The gateway container builds the gateway's handlers and API classes by autowiring. It reads their
constructor types and passes the matching services. This is how a gateway's handler receives its config,
the HTTP client, or a service of yours. [Services](../gateways/services.md) covers this from the gateway
author's side.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
