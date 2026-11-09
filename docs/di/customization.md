# Replace and add services

Payum comes with a default for every service it needs: an HTTP client, a clock, an event dispatcher, token
storage and so on. You replace a default, or add a service of your own, by registering it on
`PayumBuilder` before you call `getPayum()`. [Configure Payum](getting-started.md) lists the services and
their defaults.

## Register a service

Pass a service id and the service to `addGlobalService()`:

```php
<?php
// config.php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

$payum = (new PayumBuilder())
    ->addGlobalService(ClientInterface::class, new Psr18Client(HttpClient::create(['timeout' => 30])))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Use the id Payum looks the service up by, which is the interface name for every PSR service. A service
registered this way replaces Payum's default in every gateway, and every gateway gets the same instance.

The service can also be a closure. Payum calls it the first time something needs the service, and only
once:

```php
<?php

use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

$builder->addGlobalService(
    ClientInterface::class,
    static fn (): ClientInterface => new Psr18Client(HttpClient::create(['timeout' => 30])),
);
```

Use a closure for a service that is expensive to create, so a request that never calls a PSP does not
pay for it.

## The HTTP client and factories

Gateways call their PSP through a PSR-18 client and build requests with PSR-17 factories. By default Payum
finds an installed implementation with `php-http/discovery`. Register your own to set timeouts, a proxy or
logging on every call:

```php
<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

$factory = new Psr17Factory();

$builder
    ->addGlobalService(ClientInterface::class, new Psr18Client(HttpClient::create(['timeout' => 30])))
    ->addGlobalService(RequestFactoryInterface::class, $factory)
    ->addGlobalService(ResponseFactoryInterface::class, $factory)
    ->addGlobalService(StreamFactoryInterface::class, $factory)
    ->addGlobalService(UriFactoryInterface::class, $factory);
```

Each PSR-17 interface is looked up on its own, so the factories can be separate classes, as they are in
laminas-diactoros.

## The incoming request

Payum builds a PSR-7 `ServerRequestInterface` from PHP's globals, including the request headers, and
reads its body from `php://input`. When your framework already has the current request as PSR-7, register
that one instead:

```php
<?php

use Psr\Http\Message\ServerRequestInterface;

/** @var ServerRequestInterface $request */

$builder->addGlobalService(ServerRequestInterface::class, $request);
```

A gateway reads this request when the customer comes back from the PSP and when the PSP sends a webhook,
for example to check a signature over the raw body. Register the request Payum should read, or a gateway
checks the wrong one.

## A logger

Payum itself does not log. A gateway that asks for a PSR-3 logger in a constructor gets the one you
register:

```php
<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

$logger = new Logger('payum');
$logger->pushHandler(new StreamHandler(__DIR__ . '/var/log/payum.log'));

$builder->addGlobalService(LoggerInterface::class, $logger);
```

[Logging](../logger.md) shows what you can log and how.

## The clock

Everything in Payum that depends on the current time reads it from the PSR-20 clock, which is a
`Payum\Core\Clock\SystemClock` by default. Register your application's own clock so Payum and your
application agree on the time. In a test, register a frozen clock to test expiry:

```php
<?php

use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

$builder->addGlobalService(ClockInterface::class, new MockClock('2026-01-01 12:00:00'));
```

`MockClock` comes from `symfony/clock`. Any PSR-20 implementation works.

## The event dispatcher

Payum announces what happens to a payment, such as a status change or a webhook, to a PSR-14 event
dispatcher. The default drops every event. Register your application's dispatcher to listen:

```php
<?php

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

$builder->addGlobalService(EventDispatcherInterface::class, new EventDispatcher());
```

One dispatcher hears every gateway. [Events](../events.md) lists the events and what each one carries.

## Locks

Payum locks a payment while a command works on it, so two requests cannot capture it at once. The
default locks are files in the system's temporary directory, which only work on one server. On several
servers, register a `LockFactory` over a shared store:

```php
<?php

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;

$builder->addGlobalService(LockFactory::class, new LockFactory(new RedisStore($redis)));
```

Every gateway uses the same factory. [Concurrent requests and idempotency](../concurrency-and-idempotency.md)
covers the stores you can use and how to change how long a command waits.

## Currencies

Payum knows the ISO 4217 currencies and moneyphp's crypto currencies. To add a currency neither lists,
register your own `Money\Currencies`:

```php
<?php

use Money\Currencies;
use Money\Currencies\AggregateCurrencies;
use Money\Currencies\CryptoCurrencies;
use Money\Currencies\CurrencyList;
use Money\Currencies\ISOCurrencies;

$builder->addGlobalService(Currencies::class, new AggregateCurrencies([
    new ISOCurrencies(),
    new CryptoCurrencies(),
    new CurrencyList(['PTS' => 0]),
]));
```

`CurrencyList` maps a currency code to its number of decimal places, here loyalty points with none. Payum
uses the first list that knows a code, so keep `ISOCurrencies` first. A code you add then cannot change the
meaning of an ISO code. [Money and currencies](../money.md) covers amounts and
currencies in full.

## The Twig environment

Payum renders payment pages with Twig. When you register your application's Twig environment as `twig.env`
or `Twig\Environment`, Payum renders with it, so payment pages can use your functions, filters and globals:

```php
<?php

use Twig\Environment;

/** @var Environment $twig */

$builder->addGlobalService(Environment::class, $twig);
```

[Customize payment pages](../payment-pages.md) shows how to use your own layout and override a gateway's
templates.

## Token storage and token services

Set the token storage with `setTokenStorage()`, and the token URLs with `setTokenFactory()` and
`setGenericTokenFactoryPaths()`, as shown in [Configure Payum](getting-started.md#configure-payment-urls).

To issue tokens your own way, register your own `Payum\Core\Security\TokenIssuerInterface`. Gateways and
`Payum::getTokenIssuer()` then use it:

```php
<?php

use Payum\Core\Security\TokenIssuerInterface;

/** @var TokenIssuerInterface $issuer */

$builder->addGlobalService(TokenIssuerInterface::class, $issuer);
```

To find the token in an incoming request your own way, pass your verifier to `setHttpRequestVerifier()`. A
callable receives the token storage:

```php
<?php

use App\Payment\TokenVerifier;

$builder->setHttpRequestVerifier(static fn ($tokenStorage) => new TokenVerifier($tokenStorage));
```

`App\Payment\TokenVerifier` stands for your class that implements
`Payum\Core\Security\HttpRequestVerifierInterface`. [Tokens and payment URLs](../tokens.md) explains what a
verifier has to check.

## Reach your own services from a gateway

Payum builds a gateway's handlers and API classes by autowiring. It reads their constructor types and
passes the matching services. Every id you register with `addGlobalService()` is available this way, so a
gateway that needs a service your application provides gets it by type:

```php
<?php

use App\Fraud\FraudChecker;

$builder->addGlobalService(FraudChecker::class, static fn () => new FraudChecker());
```

A gateway class whose constructor takes a `FraudChecker` now receives this instance. Register the service
under the type the gateway asks for.

When you pass your own container with `setGlobalContainer()`, its services reach gateways the same way only
if Payum can list them. That is the case for a PHP-DI container and for a container that implements
`Payum\Core\DI\ListableContainerInterface`. For any other container, register the services a gateway needs
with `addGlobalService()` as well. [Integrate with a framework](framework-integration.md) shows both.

## Advanced: which definition wins

When the same id is registered in more than one place, your container wins over `addGlobalService()`, and
both win over Payum's defaults. A service a gateway declares for itself wins over all of them. See
[How dependency injection works](how-it-works.md#which-definition-wins) for the full order.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
