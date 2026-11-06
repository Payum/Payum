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
`FilesystemStorage` in the system temp directory for tokens and for each of `Payum\Core\Model\Payment`,
`Payout` and `ArrayObject` that you did not give a storage.

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
the host from `$_SERVER` when it builds its first URL, and throws a `LogicException` when there is none.
Building Payum and running commands that need no token work without a host.

[Tokens and payment URLs](../tokens.md) explains how tokens are issued, verified and invalidated.

## Builder methods

Every method returns the builder, so you can chain them. Call `getPayum()` last.

| Method | Default | What it changes |
| --- | --- | --- |
| `registerGateway(string $name, GatewayConfig $config)` | No gateways | Registers a gateway under a name, from its config object. |
| `addStorage(string $modelClass, StorageInterface $storage)` | The default storages | Stores models of that class in that storage. |
| `setTokenStorage(?StorageInterface $tokenStorage)` | The default storages | Stores tokens in that storage. |
| `addDefaultStorages()` | Called when no token storage is set | Registers `FilesystemStorage` in the system temp directory for tokens, `Payment`, `Payout` and `ArrayObject`, for each of them that has no storage yet. |
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
`addMiddleware()` is covered in [Middleware](../middleware.md), and `setLayout()` in
[Customize payment pages](../payment-pages.md).

The builder also has methods for gateways built by 1.x gateway factories: `addGateway()` (deprecated),
`addGatewayFactory()`, `addGatewayFactoryConfig()`, `setCoreGatewayFactory()`, `setCoreGatewayFactoryConfig()`,
`addCoreGatewayFactoryConfig()` and `setGatewayConfigStorage()`. A gateway registered with
`registerGateway()` does not use them. If you are upgrading an application, see
[Upgrade from 1.x](../upgrading.md).

## Services Payum provides

Payum creates the services it needs, such as the HTTP client, the clock, the event dispatcher and the
token services, in a container shared by every gateway. Each gateway also gets a container of its own for
its config, API and handlers. [How dependency injection works](how-it-works.md) lists every service, says
which are shared, and explains how a gateway receives them. [Replace and add services](customization.md)
shows how to replace each one.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
