# Troubleshooting

This page lists the errors you are most likely to meet while you set up Payum, what each one means and
how to fix it. Search the page for the start of the message you see.

## Building Payum

### `... is configured by ..., but ... was given.`

```
Acme\Payum\AcmeGateway is configured by Acme\Payum\AcmeConfig, but App\Payments\OtherConfig was given.
```

`registerGateway()` received a config object that belongs to a different gateway. Every gateway names
the one config class it accepts. Pass an instance of the class named after "configured by", from the
gateway's own package.

### `No PSR-18 clients found.`

A gateway needs an HTTP client to call its payment provider, and Payum could not find one installed.
Install any PSR-18 client, for example:

```bash
composer require php-http/guzzle7-adapter
```

Or register a client you already have with
`addGlobalService(Psr\Http\Client\ClientInterface::class, $client)`. See
[Replace and add services](di/customization.md).

### A deprecation notice says a gateway factory is deprecated

```
Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory is a 1.x gateway factory, which is deprecated and will not be supported in 3.0.
```

The gateway package has not been ported to Payum 2 yet. It still works. The notice is for the gateway's
maintainers, and it goes away when the package is updated. See [Supported gateways](supported-gateways.md)
and [Upgrade from 1.x](upgrading.md).

## Running a payment

### `Gateway "..." does not exist.`

```
Gateway "stripe" does not exist.
```

No gateway is registered under that name. The name is the first argument you passed to
`registerGateway()`, and it is case-sensitive. When the name comes from a token, the token was created
for a gateway that is no longer registered.

### `... does not handle ... It handles ...`

```
Gateway "acme" (Acme) does not handle Payum\Core\Command\RefundCommand. It handles Payum\Core\Command\CaptureCommand. Add a handler for Payum\Core\Command\RefundCommand to Acme\Payum\AcmeGateway::handlers().
```

The gateway does not support this operation. The message lists what it does support. Not every
provider offers every operation, so check the gateway's documentation before you offer, for example, a
refund button. The exception is `Payum\Core\Exception\CommandNotSupportedException`, and
`getSupportedCommands()` returns the same list as the message.

### `A storage for model ... was not registered.`

```
A storage for model App\Entity\Payment was not registered. There are storages for next models: Payum\Core\Model\Payment, Payum\Core\Model\ArrayObject, Payum\Core\Model\Payout.
```

Payum has to save your payment model, and you have not told it where. `addDefaultStorages()` only covers
Payum's own model classes. Register a storage for your class with `addStorage()`. See
[Storages](storages.md) and [Use your own payment model](your-order-integration.md).

## Tokens and payment URLs

### ``Token parameter `payum_token` was not found in in the http request.``

The request has no `payum_token` parameter. The customer, or a redirect in your application, reached
the capture, notify or done script without the token URL Payum generated. Always send the customer to
`$token->getTargetUrl()` exactly as it is.

### ``A token with hash `...` could not be found.``

The token no longer exists. `Payum::done()` deletes the token when it runs, so this happens when the
customer reloads the done page or opens it twice. Show the payment's status from your own records when
the token is gone. See [Tokens and payment URLs](tokens.md).

### `The current url ... not match target url ... set in the token.`

```
The current url http://shop.test/payment/capture?payum_token=... not match target url http://shop.test/capture.php?payum_token=... set in the token.
```

Payum checks that a token is used on the path it was created for. The paths differ when your routes do
not match the paths Payum uses to build token URLs, which default to `capture.php`, `notify.php`,
`authorize.php`, `refund.php`, `payout.php` and `done.php`. Set your own paths with
`setGenericTokenFactoryPaths()` on the builder. Only the path is compared, not the host.

## Payment notifications

### The provider reports that your notify URL returns `400`

`Payum::notify()` answers `400` with an empty body when the notification fails the gateway's
verification, for example because the signature does not match. Usually the webhook secret in your
gateway config is not the one the provider uses to sign.

The response does not say why, so that a forged request learns nothing. To see the reason, listen to
`CommandFailed` and log the `Payum\Core\Exception\WebhookNotVerifiedException` it carries. See
[Debugging](debugging.md) and [Payment notifications](instant-payment-notification.md).

## Loops

### `Possible endless cycle detected`

```
Possible endless cycle detected: Payum\Core\Command\SyncCommand was dispatched 100 levels deep, which reaches the limit of 100.
```

A handler executes a command whose handler, directly or through others, executes the first one again.
Payum stops the chain at 100 levels. The command named in the message is where the loop is. If you
wrote a middleware that executes commands, check it first.

With Xdebug's develop mode enabled you may see this instead:

```
Xdebug has detected a possible infinite loop, and aborted your script with a stack depth of '256' frames
```

It is the same loop. Xdebug's own limit is reached before Payum's. Turn off Xdebug, or raise
`xdebug.max_nesting_level`, to get Payum's message, which names the command.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
