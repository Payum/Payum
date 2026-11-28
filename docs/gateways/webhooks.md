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
use Payum\Core\Command\SyncCommand;

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
| returns `NotifyResult::retry()` | 503 Service Unavailable, so the PSP sends it again later |
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

## What handle() may read

`handle()` acts on the event it is given and nothing else. An application may verify a message on the
request, answer the PSP, and run `handle()` later in a queue worker, where `$context->httpRequest()` is
not the PSP's request. Anything you need from the headers, such as a timestamp or an account id, goes
into the payload in `verify()`:

```php
$event['account'] = $request->getHeaderLine('Acme-Account');

return WebhookEvent::verified($event, $event['id'], $event['type']);
```

The same goes for the answer. A PSP that wants a constant reply, such as a fixed body, gets it from
`verify()`, because the application has to send it before `handle()` has run:

```php
return WebhookEvent::verified($event, $event['id'], $event['type'], Acknowledgement::ok('[accepted]'));
```

A handler's own `NotifyResult::$acknowledgement` wins, then the event's, then `204 No Content`. Set one
on the result only when it depends on what the handler found.

{% hint style="warning" %}
PayumBundle with Messenger and `framework.remote_event` enabled sends only the acknowledgement set in
`verify()`, on the event. The handler's `NotifyResult::$acknowledgement` is not sent there, whether
the message is queued or handled in the request, because the response goes out before `handle()` has
run. Put the acknowledgement in `verify()` whenever it does not depend on what `handle()` finds.
{% endhint %}

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

A PSP with one webhook endpoint set in its dashboard sends no notify URL back to you. The next section
covers how its messages find their payment.

## Naming what the message is about

A message sent to a dashboard URL carries no token, so the application receives it on a route of its
own with `Payum::webhook()`. Payum then finds the payment from two references in the message:

- the merchant reference, which your gateway sent the PSP as metadata and the PSP echoes back,
- the PSP's own id for what the message is about, such as `pi_…` or `sub_…`.

Send the merchant reference wherever the PSP takes metadata. `$context->merchantReference()` returns it
for the payment the command is about:

```php
$checkout = $this->api->createCheckout([
    'amount' => (int) $amount->getAmount(),
    'currency' => $amount->getCurrency()->getCode(),
    'return_url' => $context->token()->getTargetUrl(),
    'metadata' => ['payum_reference' => $context->merchantReference()],
]);
```

Then return both references from `verify()`:

```php
return WebhookEvent::verified(
    $event,
    $event['id'],
    $event['type'],
    reference: $event['data']['checkout_id'],
    merchantReference: $event['data']['metadata']['payum_reference'] ?? null,
);
```

The PSP's id can also be a stored payment method's reference, such as `pm_…`, which
[Stored payment methods](stored-payment-methods.md#webhooks-about-a-method) covers.

`unverified()` takes the same two arguments. Payum looks the merchant reference up first, then the PSP's
id, then locks the payment and runs `handle()` with it as `$context->payment()`, like a message that
arrived on a notify token. The message is verified once, and the handler never looks the payment up
itself.

Payum records the PSP's id for you: the `transactionId` a handler returns, such as the one in
`CaptureResult::captured('pi_9')`, is stored against the payment after every command. The merchant
reference matters for the messages that arrive before that, while the capture is still running.

`merchantReference()` throws a `LogicException` when the gateway was not registered with
`PayumBuilder::registerGateway()`, because the reference records which gateway name owns the payment, and
when the payment has not been saved yet. By default Payum keeps the references in memory, so it forgets
them at the end of each request, or when the worker process exits under a worker server such as
RoadRunner. The application keeps them in a database, as
[Payment notifications](../instant-payment-notification.md#receive-webhooks-on-a-route-you-own) shows.

When several gateway names share one PSP account and one webhook route, the message runs on the gateway
name that recorded the reference, whichever name the route used. It never moves to a gateway of another
PSP: a payment retried on another PSP is handled by the gateway the message arrived on.

## When nothing matches

A message can name a payment that Payum has not recorded yet, or none at all. `handle()` then still runs,
with `$context->payment()` null and `$context->state()` throwing. Decide by the event type:

```php
public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
{
    if ('checkout.paid' !== $event->type) {
        return NotifyResult::ignored();
    }

    if (null === $context->payment()) {
        return NotifyResult::retry();
    }

    return NotifyResult::handled(PaymentStatus::Captured, transactionId: $event->payload['charge_id']);
}
```

Return `NotifyResult::retry()` for an event type that needs a payment. It answers 503, so the PSP sends
the message again later, by which time the payment is usually recorded. Pass an `Acknowledgement` to
answer with another status. Return `NotifyResult::ignored()` for everything else, or the PSP keeps
sending messages your gateway never acts on.

## Events that belong to no payment

Some messages name no reference Payum recorded: a dispute opened against a charge made outside Payum,
or an id the PSP puts in a field your `verify()` does not read. `$context->payment()` is then null. When
you can find the payment from what the PSP put in the message, run a payment notification for it, so
Payum records the status as it does for any other.

The same handler therefore runs twice. With no subject it is the gateway-level call: it finds the
payment and executes `NotifyCommand::forPayment($payment)->withEvent($event)`. With a subject it is the
per-payment call, and it returns the status:

```php
use Payum\Core\Result\PaymentStatus;

public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
{
    if ('charge.disputed' !== $event->type) {
        return NotifyResult::ignored();
    }

    // Per-payment call: the status is recorded from what this returns.
    if (null !== $context->payment()) {
        return NotifyResult::handled(PaymentStatus::Disputed);
    }

    // Gateway-level call: find the payment, then run the per-payment call for it.
    $payment = $this->payments->findByCheckoutId($event->payload['checkout_id']);

    if (null === $payment) {
        return NotifyResult::ignored();
    }

    $context->execute(NotifyCommand::forPayment($payment)->withEvent($event));

    return NotifyResult::ignored();
}
```

`$this->payments` stands for your own lookup, such as a Payum storage. `withEvent()` hands the command
the verified event, so it is not verified a second time. The check on `$context->payment()` is what ends
the recursion: without it the per-payment call would look the payment up and execute again.

## The request the handler reads

`verify()` gets the request from the gateway's container, not the one passed to `Payum::notify()`.
`Payum::webhook()` is the exception: given a request, it verifies that request instead. By
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
