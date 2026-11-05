# Configuration

A gateway's config holds what an application must supply to use it: credentials, and switches such as
the sandbox. It is a value object that checks itself in its constructor, so a missing or malformed
credential fails when the application starts, with a stack trace that points at the application's own
wiring, rather than inside a handler halfway through a payment.

## Write the config class

Implement `Payum\Core\Config\GatewayConfig`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\InvalidArgumentException;

final class AcmeConfig implements GatewayConfig
{
    public function __construct(
        public readonly string $apiKey,
        public readonly bool $sandbox = false,
    ) {
        if ('' === $apiKey) {
            throw new InvalidArgumentException('Acme needs an API key.');
        }
    }

    public function getGatewayClass(): string
    {
        return AcmeGateway::class;
    }

    public function baseUrl(): string
    {
        return $this->sandbox ? 'https://sandbox.acme.test/v1' : 'https://api.acme.test/v1';
    }
}
```

`getGatewayClass()` is how Payum gets from a config to the gateway it configures. The gateway's
`configClass()` points the other way, for an application that has a gateway but no config yet. The two
must name each other.

Make the properties `readonly`. A gateway's container holds one config instance for its whole lifetime,
so nothing should be able to change a credential after it is built.

## Register a gateway

An application passes an instance to `PayumBuilder::registerGateway()`, with the name it wants to use
for the gateway:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();

$gateway = $payum->getGateway('acme');
```

That is all the wiring there is. The config names its gateway, the gateway names its handlers, and
Payum builds the gateway's container from there.

`registerGateway()` creates the gateway and compares its `configClass()` with the config it was given.
A config that belongs to another gateway throws a `Payum\Core\Exception\LogicException` straight away,
with a message of this form:

```
Acme\Payum\AcmeGateway is configured by Acme\Payum\AcmeConfig, but Acme\Payum\OtherConfig was given.
```

## Register one gateway several times

Register the same gateway under different names with different configs, for example one account per
region:

```php
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme_eu', new AcmeConfig(apiKey: 'sk_eu_123'))
    ->registerGateway('acme_us', new AcmeConfig(apiKey: 'sk_us_456'))
    ->getPayum();
```

Each name gets its own container, and so its own config, API object and handlers. Services that Payum
shares, such as the HTTP client and the token storage, are the same instance for both.

## Read the config in a service

Payum registers the config in the gateway's container under its own class and under
`Payum\Core\Config\GatewayConfig`. Any class built from that container can take it in its constructor,
which is how the API class gets its credentials:

```php
final class AcmeApi
{
    public function __construct(
        private readonly AcmeConfig $config,
        private readonly ClientInterface $httpClient,
    ) {
    }
}
```

Handlers usually take the API class rather than the config, so credentials stay in one place.

## Describe a gateway before it is configured

Because a gateway takes no constructor arguments, an application can describe one nobody has
configured yet. This is what an "add a payment method" screen is built from:

```php
$gateway = new AcmeGateway();

$gateway->name();         // 'Acme Payments'
$gateway->logo();         // a Payum\Core\Metadata\Logo
$gateway->websiteUrl();   // a League\Uri\Uri
$gateway->configClass();  // 'Acme\Payum\AcmeConfig', the form to render
```

The config class's constructor parameters are the fields that form needs.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
