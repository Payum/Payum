# Webhooks

A webhook is a message a PSP sends to your application when something happens to a payment after the
customer has left: it settled, it was disputed, the customer's bank finally answered. A gateway
receives these by shipping a notify handler. This page covers the gateway's side. The application's
side, the notify script, is in [Payment notifications](../instant-payment-notification.md).

## Write the notify handler

Implement `Payum\Core\Handler\NotifyHandlerInterface`. It has two methods: `verify()` checks that the
message really came from the PSP, and `handle()` acts on it.

```php
<?php

declare(strict_types=1);

namespace Acme\Payum\Handler;

use Acme\Payum\AcmeApi;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Psr\Http\Message\ServerRequestInterface;

final class NotifyHandler implements NotifyHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
    ) {
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        $payload = (string) $request->getBody();

        if (! hash_equals($this->api->sign($payload), $request->getHeaderLine('Acme-Signature'))) {
            throw new WebhookNotVerifiedException('The Acme-Signature header does not match the body.');
        }

        $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        return WebhookEvent::verified($event, $event['id'], $event['type']);
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        if ('checkout.paid' !== $event->type || null === $context->payment()) {
            return NotifyResult::ignored();
        }

        if ($context->state()['checkout_id'] !== $event->payload['checkout_id']) {
            return NotifyResult::ignored();
        }

        return NotifyResult::handled(PaymentStatus::Captured, transactionId: $event->payload['charge_id']);
    }
}
```

Acme signs each message with an HMAC of the body, so the API class gets one more method:

```php
public function sign(string $payload): string
{
    return hash_hmac('sha256', $payload, $this->config->apiKey);
}
```

List the handler in the gateway's `handlers()` like any other. That gives the gateway
`Capability::Webhooks`.

## Verify the message

Read the signed bytes with `(string) $request->getBody()`. Most PSPs sign the raw body, and a JSON body
never appears in `getParsedBody()`. Compare signatures with `hash_equals()`, which takes the same time
whether or not they match, so an attacker cannot guess a signature one byte at a time.

When the message is not genuine, throw `Payum\Core\Exception\WebhookNotVerifiedException`.
`Payum::notify()` then answers 400 with an empty body and leaves the payment alone. The exception's
message is for the application's log: telling the sender which check failed only helps an attacker.

Keep `verify()` to the request. Do not load the payment there: it has to answer fast, and anything that
depends on stored data is handling.

{% hint style="warning" %}
A valid signature proves the PSP sent the message, not that it is about this payment. Payum applies
the status to the payment the notify token points at, so a genuine message replayed against another
payment's notify URL would mark that payment too. Compare the PSP's reference in the message with the
one you stored, as the handler above does with `checkout_id`.
{% endhint %}

## Reject old messages

A PSP that puts a timestamp in its messages lets you refuse one that is replayed hours later. Take
`Psr\Clock\ClockInterface` in the constructor and compare in `verify()`:

```php
use Psr\Clock\ClockInterface;

public function __construct(
    private readonly AcmeApi $api,
    private readonly ClockInterface $clock,
) {
}

public function verify(ServerRequestInterface $request): WebhookEvent
{
    // ... check the signature and decode $event as above

    if ($event['created'] < $this->clock->now()->getTimestamp() - 300) {
        throw new WebhookNotVerifiedException('The message is more than five minutes old.');
    }

    return WebhookEvent::verified($event, $event['id'], $event['type']);
}
```

Payum shares one clock with every gateway, so a test can fix the time with
`PayumBuilder::addGlobalService(ClockInterface::class, $clock)`.

## When the PSP signs nothing

Some PSPs do not sign their messages. Say so with `WebhookEvent::unverified()`, rather than leaving the
check out:

```php
public function verify(ServerRequestInterface $request): WebhookEvent
{
    return WebhookEvent::unverified((array) $request->getParsedBody());
}
```

Then do not act on what the message says. Treat it as a hint and ask the PSP for the real state with a
`SyncCommand`:

```php
public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
{
    $payment = $context->payment();

    if (null === $payment) {
        return NotifyResult::ignored();
    }

    $synced = $context->execute(SyncCommand::forPayment($payment));

    return NotifyResult::handled($synced->status);
}
```

That costs one request to the PSP per message, and needs a sync handler. If one handler covers both
kinds of message, `$event->isVerified()` tells them apart.

## Answer the PSP

A PSP sends a message again until it gets an answer it counts as success, so the answer matters:

| The handler | The PSP gets |
| :--- | :--- |
| returns `NotifyResult::handled(...)` | 204 No Content |
| returns `NotifyResult::ignored()` | 204 No Content |
| returns `NotifyResult::handled($status, Acknowledgement::ok('[accepted]'))` | 200 with the body `[accepted]` |
| throws `WebhookNotVerifiedException` | 400 with an empty body |

Most PSPs accept any 2xx. Set an acknowledgement only when yours wants a particular answer:

```php
use Payum\Core\Result\Acknowledgement;

Acknowledgement::noContent();       // 204
Acknowledgement::ok('[accepted]');  // 200 with a body
new Acknowledgement(200, '{"ok":true}', ['Content-Type' => 'application/json']);
```

Return `NotifyResult::ignored()` for an event type your gateway does not use. Rejecting a message the
gateway does not recognise makes the PSP send it again and again.

## Give the PSP a notify URL

PSPs that take a notification address per payment ask for it when the checkout is created. Get it from
the context:

```php
$checkout = $this->api->createCheckout([
    'amount' => (int) $amount->getAmount(),
    'currency' => $amount->getCurrency()->getCode(),
    'return_url' => $context->token()->getTargetUrl(),
    'notify_url' => $context->notifyUrl(),
]);
```

`notifyUrl()` creates a long-lived notify token that points at this payment and returns its URL.
`notifyToken()` returns the token itself. Both throw a `LogicException` for a gateway that was not
registered with `PayumBuilder::registerGateway()`, because a token needs the gateway's name.

It creates a new token on every execution. Keep the first one in the state, so a customer who starts
the checkout again does not get a second token:

```php
$state = $context->state();

$state['notify_url'] ??= $context->notifyUrl();
```

For a PSP with one webhook endpoint set in its dashboard, the application routes that endpoint itself
and dispatches `NotifyCommand::forGateway()`. The command then points at no payment:
`$context->payment()` is null and `$context->state()` throws, so the handler has to find the payment
from the message itself.

An application that has already found what the message is about dispatches `NotifyCommand::forPayment()`
or, for a payout, `NotifyCommand::forPayout()`. The handler then reads it from `$context->payment()` or
`$context->payout()`.

## The request the handler reads

`verify()` gets the request from the gateway's container, not the one passed to `Payum::notify()`. By
default Payum builds it from PHP's globals and reads the body from `php://input`. In a worker server
such as RoadRunner or FrankenPHP, or after a framework has already read the body, that read is empty:
every signature check fails and every message gets a 400.

An application in that situation registers the real request, and Payum uses it instead:

```php
$builder->addGlobalService(ServerRequestInterface::class, $request);
```

Mention this in your gateway's documentation if it reads the body.

## Test the handler

`verify()` needs only a request, so you can test it without Payum. Build the handler and a request with
PSR-17 factories:

```php
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

$handler = new NotifyHandler(new AcmeApi(
    new AcmeConfig(apiKey: 'sk_test_123', sandbox: true),
    Psr18ClientDiscovery::find(),
    Psr17FactoryDiscovery::findRequestFactory(),
    Psr17FactoryDiscovery::findStreamFactory(),
));

$request = Psr17FactoryDiscovery::findServerRequestFactory()
    ->createServerRequest('POST', '/notify.php')
    ->withHeader('Acme-Signature', 'not-the-signature')
    ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream('{"id":"evt_1"}'));

$this->expectException(WebhookNotVerifiedException::class);

$handler->verify($request);
```

For the whole path, register the request with `addGlobalService()` and dispatch the command:

```php
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(ServerRequestInterface::class, $request)
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();

$result = $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));
```

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
