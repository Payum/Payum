# Defining a gateway

The gateway class is the whole definition of a gateway: what it is called, what its logo is, which
config class it takes and which handlers it ships. Payum builds everything else from it.

## The gateway class

Implement `Payum\Core\Gateway\GatewayInterface`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Acme\Payum\Handler\CaptureHandler;
use Acme\Payum\Handler\NotifyHandler;
use Acme\Payum\Handler\RefundHandler;
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
        return Logo\Path::create(__DIR__ . '/Resources/logo.svg');
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
            NotifyHandler::class,
        ];
    }
}
```

| Method | Returns |
| :--- | :--- |
| `name()` | The name an application shows, such as `'Acme Payments'` |
| `logo()` | A `Payum\Core\Metadata\Logo` |
| `websiteUrl()` | The PSP's website or developer documentation, as a `League\Uri\Uri` |
| `configClass()` | The config class an application passes to `registerGateway()` |
| `handlers()` | The handler classes, in any order. See [Handlers](handlers.md) |

## Take no constructor arguments

A gateway must be constructible with no arguments. Payum creates one only to read its metadata, which
is what lets an application list every installed gateway, with its name, logo and config class, before
anyone has entered credentials.

So credentials never go in the gateway class. They belong in the [config](configuration.md), which
Payum puts in the gateway's container. This also means metadata cannot depend on configuration: there
is no per-merchant name, and no capability that depends on the merchant's account.

## Lay out the package

Keep the gateway class at the root of the namespace, beside its config and API:

```
src/
├── AcmeGateway.php
├── AcmeConfig.php
├── AcmeApi.php
├── Handler/
│   ├── CaptureHandler.php
│   ├── NotifyHandler.php
│   └── RefundHandler.php
└── Resources/
    ├── logo.svg
    └── views/
```

## The logo

`Logo` has three implementations, so an application can render it however it needs:

```php
Logo\Path::create(__DIR__ . '/Resources/logo.svg');       // a file shipped with the package
Logo\Url::create('https://acme.test/logo.svg');           // a public URL
Logo\Base64Encode::create('data:image/svg+xml;base64,...'); // inline data
```

Each one returns its value from `getValue()`. Shipping the file with `Logo\Path` avoids a network
request and a broken link when the PSP changes its website.

## Capabilities

`Payum\Core\Gateway\Capability` lists what a gateway can do. Payum works out the operations from
`handlers()`: a gateway that ships a capture handler supports `Capability::Capture`, so the two cannot
disagree.

| Capability | Where it comes from |
| :--- | :--- |
| `Authorize`, `Cancel`, `Capture`, `Payout`, `Refund`, `Sync` | The handler for that command |
| `Webhooks` | A notify handler. See [Webhooks](webhooks.md) |
| `MultiCurrency`, `PartialCapture`, `PartialRefund`, `Recurring`, `StoredPaymentMethods`, `ThreeDSecure` | Declared by the gateway |

To declare the rest, implement `Payum\Core\Gateway\DeclaresCapabilities`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

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
        ];
    }

    // name(), logo(), websiteUrl(), configClass() and handlers() as above
}
```

Return only capabilities that no handler implies. Listing `Capability::Capture` here as well gives two
places to keep in step, and they drift.

Capabilities tell an application what to offer, for example whether to show a "refund part of this
order" button. Payum does not enforce them: a caller can still ask for a partial refund from a gateway
that does not declare `PartialRefund`, so a handler for a PSP that cannot do one should throw. The enum is
string-backed, so an application can store a capability list or send it as JSON.

An application reads the capabilities a gateway's handlers imply with `HandlerMap`, and the declared ones
from the gateway itself:

```php
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Handler\HandlerMap;

$gateway = new AcmeGateway();

$capabilities = HandlerMap::fromHandlers($gateway->handlers())->capabilities();

if ($gateway instanceof DeclaresCapabilities) {
    $capabilities = [...$capabilities, ...$gateway->capabilities()];
}
```

## Optional interfaces

A gateway implements more interfaces only when it needs what they add:

| Interface | Implement it when | See |
| :--- | :--- | :--- |
| `Payum\Core\Gateway\DeclaresCapabilities` | The gateway supports something its handlers cannot imply | [Capabilities](#capabilities) |
| `Payum\Core\Gateway\DeclaresTemplates` | The gateway ships Twig templates | [Templates](templates.md) |
| `Payum\Core\Gateway\DeclaresMiddleware` | The gateway needs middleware that applies to no other gateway | [Middleware](middleware.md) |
| `Payum\Core\DI\ContainerConfiguration` | A service cannot be autowired, or the gateway replaces one | [Services](services.md) |
