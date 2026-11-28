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
instead take one URL that you enter in their dashboard. That URL carries no token, so you route it
yourself, as [Receive webhooks on a route you own](#receive-webhooks-on-a-route-you-own) shows.

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

## Receive webhooks on a route you own

A provider that sends every event to one URL in its dashboard, such as `https://shop.example/webhooks/acme`,
needs a route that names the gateway. `Payum::webhook()` takes the gateway name and answers the provider:

```php
<?php
// webhooks/acme.php

require __DIR__.'/config.php';

$payum->webhook('acme')->send();
```

The gateway verifies the message, finds the payment from the references in it, and records the status
the same way as a notification on a notify URL. It answers like `Payum::notify()`:

| What happened | The provider gets |
| :--- | :--- |
| the message was handled or ignored | 204 No Content, or the answer the gateway chose |
| the gateway found no payment and asked for the message again | 503, so the provider sends it again later |
| the message failed verification | 400 with an empty body |

In a framework, pass the request. The gateway then verifies that request instead of reading its
`ServerRequestInterface` service:

```php
return $payum->webhook('acme', $request);
```

`Payum::webhook()` returns a `Symfony\Component\HttpFoundation\Response` and needs
`symfony/http-foundation`, as `Payum::notify()` does.

### Keep the references in a database

Payum finds the payment from references it recorded when the payment was created, so two things need to
be in place:

- Register the gateway by name with `PayumBuilder::registerGateway()`. The references record which gateway
  name owns each payment.
- Keep the references somewhere that outlives the request. Payum configures an index by default, but it
  keeps the references in memory. It forgets them at the end of each request, or when the worker process
  exits under a worker server such as RoadRunner, so a webhook that arrives later finds nothing.

To keep the references in Doctrine, extend Payum's `ReferenceRecord` model:

```php
<?php

namespace Acme\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\ReferenceRecord as BaseReferenceRecord;

#[ORM\Entity]
#[ORM\Table(name: 'payum_reference_record')]
class ReferenceRecord extends BaseReferenceRecord
{
}
```

Its columns come from Payum's XML mapping, which you register the way
[Storages](storages.md#create-the-entity-manager) shows. Then pass the index to the builder:

```php
use Acme\Entity\ReferenceRecord;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Reference\StorageReferenceIndex;

$builder->setReferenceIndex(
    new StorageReferenceIndex(new DoctrineStorage($entityManager, ReferenceRecord::class)),
);
```

`StorageReferenceIndex` takes any Payum storage of `ReferenceRecord` models identified by their `key`
field, and Payum ships a Doctrine MongoDB ODM mapping for the model too. Never delete the rows: a provider
can send a message about a payment months after it was made.

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

## Verify now, handle later

A provider wants its answer within seconds, and the work a notification starts may take longer. Verify
the message on the request, answer, and let a worker do the rest. `verifyWebhook()` runs only the
gateway's check and returns the verified event:

```php
<?php

use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Result\Acknowledgement;

try {
    $event = $payum->getGateway($token->getGatewayName())->verifyWebhook(ServerRequest::fromGlobals());
} catch (WebhookNotVerifiedException) {
    http_response_code(400);

    return;
}

$queue->push(serialize([$token->getHash(), $event]));

$acknowledgement = $event->acknowledgement ?? Acknowledgement::noContent();
http_response_code($acknowledgement->status);
echo $acknowledgement->body;
```

`$queue` stands for your own queue. A `WebhookEvent` serializes, so it travels in a job. In the worker,
pass it to the command with `withEvent()`:

```php
$command = NotifyCommand::forToken($token)->withEvent($event);

$payum->getGateway($token->getGatewayName())->execute($command);
```

A command that carries an event skips verification, and does everything else: it runs the middleware,
dispatches `WebhookReceived`, calls the handler and records the status. The worker does not need the
PSP's request, and nothing is verified twice.

`verifyWebhook()` throws a `LogicException` for a gateway that has no notify handler, or one written for
Payum 1.x.

### When the message is rejected

A message that fails verification dispatches `Payum\Core\Event\WebhookRejected` before the exception is
thrown. It has the `gatewayName`, the `request` and the `exception`, and no command or context, because
none exists yet. Listen for it to log a probe or alert on a run of them:

```php
use Payum\Core\Event\WebhookRejected;

$dispatcher->addListener(WebhookRejected::class, function (WebhookRejected $event) use ($logger): void {
    $logger->warning('Rejected a {gateway} webhook: {reason}', [
        'gateway' => $event->gatewayName,
        'reason' => $event->exception->getMessage(),
    ]);
});
```

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
