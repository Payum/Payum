# Defining a gateway

A gateway class is the whole definition: what it is called, what it looks like, which config it takes, and which handlers it ships. It replaces the gateway factory.

```php
<?php
namespace Acme\Payum;

use League\Uri\Uri;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;

final class AcmeGateway implements GatewayInterface
{
    public function name(): string
    {
        return 'Acme Payments';
    }

    public function logo(): Logo
    {
        return Logo\Url::create('https://acme.test/logo.svg');
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://developer.acme.test');
    }

    public function configClass(): string
    {
        return AcmeConfig::class;
    }

    public function handlers(): array
    {
        return [
            CaptureHandler::class,
            RefundHandler::class,
        ];
    }
}
```

### No constructor arguments

A gateway must be constructible with no required arguments. Payum instantiates one purely to read its metadata, which is what lets an application list every installed gateway — name, logo, and which config class to ask for — **before any credentials exist**. That is what an "add a payment method" screen needs.

Credentials live in the config object and are resolved from the container. Do not take them here.

One consequence worth knowing: metadata cannot vary with configuration. There is no per-merchant title, and no capability that depends on the merchant's account.

### Where it goes

Put the gateway class at the root of its package, beside the config and the api:

```
src/Payum/Acme/
├── AcmeGateway.php          the gateway
├── Config/AcmeConfig.php    the credentials
├── Api/AcmeApi.php          talks to the PSP
└── Handler/
    ├── CaptureHandler.php
    └── RefundHandler.php
```

### The logo

`Logo` resolves to one of three things, so an application can render it however it likes:

```php
Logo\Url::create('https://acme.test/logo.svg');           // publicly reachable
Logo\Path::create(__DIR__ . '/Resources/logo.svg');       // on disk, bundled with the package
Logo\Base64Encode::create('data:image/svg+xml;base64,…'); // inline, no network
```

Bundling the file with `Logo\Path` avoids both a network call and a dead link.

### Capabilities

Payum derives the operation capabilities from `handlers()` — a gateway shipping a `CaptureHandler` supports `Capability::Capture`, and the two can never disagree. Declare only what a handler list cannot imply, by implementing `DeclaresCapabilities`:

```php
<?php
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresCapabilities
{
    public function capabilities(): array
    {
        return [
            Capability::PartialRefund,
            Capability::MultiCurrency,
            Capability::ThreeDSecure,
            Capability::Webhooks,
        ];
    }

    // …
}
```

Do not list `Capability::Capture` here. It is already implied by `handlers()`, and repeating it creates a second source of truth that will drift.

The full set:

| Derived from `handlers()` | Declared |
| :--- | :--- |
| `Authorize`, `Cancel`, `Capture`, `Payout`, `Refund` | `MultiCurrency`, `PartialCapture`, `PartialRefund`, `Recurring`, `StoredPaymentMethods`, `ThreeDSecure`, `Webhooks` |

The enum is string-backed, so a capability list can be persisted, sent as JSON, or rendered in an admin screen.

### Declaring services

Most gateways declare none — see [Services](services.md). When autowiring cannot reach something, implement `ContainerConfiguration`:

```php
<?php
use Payum\Core\DI\ContainerConfiguration;

final class AcmeGateway implements GatewayInterface, ContainerConfiguration
{
    public function configureContainer(): array
    {
        return [
            AcmeApi::class => static fn (ContainerInterface $c): AcmeApi => new AcmeApi(
                $c->get(AcmeConfig::class)->toArray(),
                $c->get(ClientInterface::class),
            ),
        ];
    }

    // …
}
```

Next: [Configuration](configuration.md).
