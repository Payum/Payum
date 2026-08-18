# Webhooks

A PSP sends a message when something happens to a payment — it settled, it was disputed, a customer's
bank finally answered. A gateway receives those by shipping a notify handler.

### The handler

Two methods. Checking that a message is genuine and acting on what it says are separate jobs, and Payum
keeps them apart: verification is cheap, security-critical and has to answer immediately, while handling
is the slow part.

```php
<?php
namespace Acme\Payum\Handler;

use Acme\Payum\Api\AcmeApi;
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
    public function __construct(private readonly AcmeApi $api)
    {
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        $payload = (string) $request->getBody();

        if (! hash_equals($this->api->sign($payload), $request->getHeaderLine('Acme-Signature'))) {
            throw new WebhookNotVerifiedException('The signature does not match.');
        }

        $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        return WebhookEvent::verified($event, $event['id'], $event['type']);
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        if ('payment.captured' !== $event->type) {
            return NotifyResult::ignored();
        }

        if ($context->state()['checkout_id'] !== $event->payload['checkout_id']) {
            // Genuine, but about a different payment -- do not act on it.
            return NotifyResult::ignored();
        }

        return NotifyResult::handled(PaymentStatus::Captured, transactionId: $event->payload['transaction']);
    }
}
```

List it in `handlers()` like any other handler. That is what gives the gateway `Capability::Webhooks`.

### Verifying

Use `hash_equals()` rather than `===` to compare a signature, and read the signed bytes with
`(string) $request->getBody()` — most PSPs sign the raw body, and a JSON payload never reaches
`getParsedBody()`.

Do not reach for the payment in `verify()`. Verification that depends on stored state is handling in
disguise, and this is the method that has to stay fast.

Throw `WebhookNotVerifiedException` when the message is not genuine. Payum answers the caller 400 with
an empty body and leaves the payment untouched. The exception's message is for your log — telling
whoever sent the message which check failed only helps them get it right next time.

Verifying proves the message came from the PSP. It does not prove the message is about *this* payment.
Core applies the result's status to the subject resolved from the notify token, never from the event —
so a genuine, correctly signed event replayed against a different payment's notify URL would mark that
payment captured too. Compare the PSP's own reference in the event against the one you stored when you
created the checkout, as the handler above does, and decline the event on a mismatch.

### The request body

`Payum::notify($request)` uses the request you pass it only to verify the token in the URL. The PSR-7
request a handler's `verify()` sees comes from the container instead, and by default that reads
`php://input`. Under RoadRunner or FrankenPHP, where nothing lands in `php://input`, or once a framework
has already consumed the body, that read comes back empty — every signature check then fails, and every
message gets a silent 400 that is hard to debug.

Register your own request and Payum uses that instead:

```php
$payumBuilder->addGlobalService(ServerRequestInterface::class, $request);
```

### When the PSP signs nothing

Some do not. Say so, rather than leaving the check out:

```php
public function verify(ServerRequestInterface $request): WebhookEvent
{
    return WebhookEvent::unverified((array) $request->getParsedBody());
}
```

Then do not act on what the message says — treat it as a nudge and re-read the real state from the PSP:

```php
public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
{
    $payment = $context->payment();

    if (! $payment instanceof PaymentInterface) {
        return NotifyResult::ignored();
    }

    $synced = $context->execute(SyncCommand::forPayment($payment));

    return NotifyResult::handled($synced->status);
}
```

It costs a request per message, and it is safe. `$event->isVerified()` tells the two apart if one
handler covers both.

### Answering the PSP

A PSP retries anything it does not consider a success, so what you answer matters.

| You return | The PSP gets |
| :--- | :--- |
| `NotifyResult::handled(...)` | 204 No Content |
| `NotifyResult::ignored()` | 204 No Content |
| `NotifyResult::handled($status, Acknowledgement::ok('[accepted]'))` | 200, body `[accepted]` |
| `throw new WebhookNotVerifiedException(...)` | 400, empty body |

Most PSPs accept any 2xx, so leave the acknowledgement out unless yours is particular — Adyen accepts
only the body `[accepted]`:

```php
use Payum\Core\Result\Acknowledgement;

Acknowledgement::noContent();               // 204
Acknowledgement::ok('[accepted]');          // 200 with a body
new Acknowledgement(200, '{"ok":true}', ['Content-Type' => 'application/json']);
```

Return `NotifyResult::ignored()` for an event type your gateway has no interest in. Rejecting a message
because you did not recognise it makes the PSP redeliver it on a backoff schedule for as long as it
keeps failing.

### The notify URL

Gateways that take a notification address per payment ask for it when the payment is created. Ask the
context:

```php
$checkout = $this->api->createCheckout([
    'return_url' => $context->token()?->getTargetUrl(),
    'notify_url' => $context->notifyUrl(),
    'amount' => $context->payment()?->getTotalAmount(),
]);
```

`notifyUrl()` mints a long-lived token pointed at this payment and returns its URL. `notifyToken()`
gives you the token itself if you want its hash.

It mints once per execution, not once per payment. A customer who abandons a checkout and starts again
gets a second URL unless you keep the first:

```php
$state = $context->state();

$state['notify_url'] ??= $context->notifyUrl();
```

Gateways with a single endpoint configured in the PSP's dashboard can skip minting one of these per
payment — point that one endpoint at your notify script and Payum still works out which gateway a
message belongs to from the token in the URL. But a token minted for the gateway alone carries no
payment: `$context->payment()` is null and `$context->state()` throws, so there is nothing for Payum to
record a status onto. That handler has to find the payment itself from the event payload.

### Testing one

A notify handler is two plain methods, so most of it needs no gateway at all:

```php
$handler = new NotifyHandler(new AcmeApi('secret'));

$this->expectException(WebhookNotVerifiedException::class);

$handler->verify($requestWithABadSignature);
```

For the whole path, register your own request so the body is one you control:

```php
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(ServerRequestInterface::class, $request)
    ->registerGateway('acme', new AcmeConfig())
    ->getPayum();

$result = $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));
```

Next: [Migrating a gateway from 1.x](migrating-from-v1.md).
