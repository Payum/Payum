# Payment notifications

Some things happen to a payment while nobody is on your site: a bank transfer clears, a refund settles
days later, a cardholder opens a dispute. The provider tells you by calling a URL on your server, which is
called a notification or a webhook. Payum receives the call, checks that it really came from the
provider, records what it says on the payment and answers the provider.

This page covers the application side. If you are writing a gateway, see [Webhooks](gateways/webhooks.md).

## How a notification finds its payment

A notification arrives on a notify URL, which is a token URL that points at `notify.php` and names the
gateway and the payment. You do not create it. A gateway whose provider takes a notification URL per
payment creates the token while it runs the capture and sends its URL to the provider. Some providers
instead take one URL that you enter in their dashboard; the gateway's documentation says what to enter.

A notify token is not deleted after use, because the provider may call the same URL many times for one
payment. [Tokens and payment URLs](tokens.md) explains tokens and how to move `notify.php` to another
path.

## Write the notify script

The notify script verifies the token, runs a `NotifyCommand` and sends the answer the gateway chose:

```php
<?php
// notify.php

use GuzzleHttp\Psr7\ServerRequest;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Result\Acknowledgement;

require __DIR__.'/config.php';

try {
    $token = $payum->getHttpRequestVerifier()->verify(ServerRequest::fromGlobals());
} catch (InvalidArgumentException) {
    http_response_code(404);

    return;
}

try {
    $result = $payum->getGateway($token->getGatewayName())->execute(NotifyCommand::forToken($token));
} catch (WebhookNotVerifiedException $e) {
    error_log('Rejected a notification: '.$e->getMessage());
    http_response_code(400);

    return;
}

$acknowledgement = $result->acknowledgement ?? Acknowledgement::noContent();

http_response_code($acknowledgement->status);
foreach ($acknowledgement->headers as $name => $value) {
    header($name.': '.$value);
}
echo $acknowledgement->body;
```

`config.php` is the one from [Payment operations](payment-operations.md#set-up). The gateway checks the
provider's signature against the headers and raw body of the current request, which Payum reads from
PHP's globals and `php://input`.

Most providers accept any 2xx answer, so a gateway that names no answer gets `204 No Content`. A gateway
whose provider expects a particular body, such as a fixed string, sets the acknowledgement for you.

### In a framework

When your framework gives you a Symfony HttpFoundation request, `Payum::notify()` does the same work and
returns a `Symfony\Component\HttpFoundation\Response`:

```php
return $payum->notify($request);
```

It answers `400` with an empty body when the notification fails verification, and lets the token
verifier's `InvalidArgumentException` through for a bad token. `Payum::notify()` needs
`symfony/http-foundation`, which Payum does not install for you.

Register the framework's request, converted to PSR-7, as the `ServerRequestInterface` service as well.
`Payum::notify()` only reads the token from the request you pass it. The gateway verifies the signature
against the `ServerRequestInterface` service. The request Payum builds by default reads its body from
`php://input`, which is empty under RoadRunner or FrankenPHP and once a framework has read the body. See [Integrate with a framework](di/framework-integration.md).

## What Payum does with a notification

When the notification is genuine, Payum:

1. records the status the gateway read from it on the payment, if the payment implements
   `StatusAwareInterface`,
2. dispatches a `StatusChanged` event if the status moved,
3. saves the payment,
4. answers the provider.

A gateway answers an event it does not care about with a success too, and leaves the payment alone.
Rejecting it would make the provider send it again and again.

## When verification fails

When the gateway cannot confirm that the provider sent the message, or the message was changed on the
way, the command throws `Payum\Core\Exception\WebhookNotVerifiedException`. The payment is not touched.
Answer `400` with an empty body, as the script does. Whoever sent the message is either misconfigured or
probing, and neither should learn which check failed. Log the exception's message for yourself.

`Payum::notify()` catches the exception and answers `400` without passing it on. To log it there, listen
for the `Payum\Core\Event\CommandFailed` event, whose `exception` property holds it. See
[Events](events.md).

A run of `400` answers usually means a configuration problem: the wrong webhook secret in the gateway's
config, or a request whose body was read before the gateway saw it.

## React to a notification

Do not put order logic in the notify script. Listen for the status change instead, which also covers
changes that come from a capture or a sync:

```php
<?php

use Payum\Core\Event\StatusChanged;
use Payum\Core\Result\PaymentStatus;

$dispatcher->addListener(StatusChanged::class, function (StatusChanged $event) use ($orders): void {
    match ($event->to) {
        PaymentStatus::Captured => $orders->markPaid($event->subject),
        PaymentStatus::ChargedBack => $orders->flagChargeback($event->subject),
        default => null,
    };
});
```

`$orders` stands for your own code. The event fires only when the status really changes, so a provider
that sends the same notification twice does not mark the order paid twice.

The listener runs before Payum saves the payment. If it hands work to another process, such as a queued
job, put the new status in the message: the job may load the payment before the save and still see the
old status.
[Payment status](payment-status.md#react-when-the-status-changes) has the details.

## Make the endpoint reachable

The provider's servers call the notify URL directly, with no browser and no session. Make sure the URL:

- is reachable from the internet, over HTTPS,
- needs no login, and is left out of CSRF protection,
- answers the request itself instead of redirecting.

A provider retries a delivery that fails. It does not follow a redirect or fill in a login form. To test
on your own machine, expose your local server through a tunnel and use the URL it gives you.

## If a notification never arrives

Networks drop requests and providers have outages, so a payment can sit at `Pending` after it has
cleared. Ask the provider directly with a `SyncCommand`, from a job that walks the payments still open:

```php
<?php

use Payum\Core\Command\SyncCommand;

foreach ($pendingPayments as $payment) {
    $payum->getGateway('acme')->execute(SyncCommand::forPayment($payment));

    $payum->getStorage($payment::class)->update($payment);
}
```

Payum records whatever status the provider reports. Not every gateway supports a sync. See
[Payment operations](payment-operations.md#ask-the-provider-for-the-current-state).

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
