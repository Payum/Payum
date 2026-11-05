# Handlers

A handler answers one kind of command. Each command has its own handler interface, so `handle()` is
typed on both sides: it takes that command and returns that command's result.

| Command | Handler interface |
| :--- | :--- |
| `CaptureCommand` | `Payum\Core\Handler\CaptureHandlerInterface` |
| `AuthorizeCommand` | `Payum\Core\Handler\AuthorizeHandlerInterface` |
| `RefundCommand` | `Payum\Core\Handler\RefundHandlerInterface` |
| `CancelCommand` | `Payum\Core\Handler\CancelHandlerInterface` |
| `PayoutCommand` | `Payum\Core\Handler\PayoutHandlerInterface` |
| `SyncCommand` | `Payum\Core\Handler\SyncHandlerInterface` |
| `NotifyCommand` | `Payum\Core\Handler\NotifyHandlerInterface` |

## Write a handler

This refund handler asks Acme to refund the checkout the capture handler stored, and reports whether
the refund covered the whole payment:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum\Handler;

use Acme\Payum\AcmeApi;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\RefundHandlerInterface;
use Payum\Core\Money\Amount;
use Payum\Core\Result\RefundResult;

final class RefundHandler implements RefundHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
    ) {
    }

    public function handle(RefundCommand $command, Context $context): RefundResult
    {
        $amount = $context->amount();

        $refund = $this->api->refund($context->state()['checkout_id'], $amount);

        return $amount->equals(Amount::of($context->payment()))
            ? RefundResult::refunded($refund['id'], $amount)
            : RefundResult::partiallyRefunded($refund['id'], $amount);
    }
}
```

It needs one more method on the API class:

```php
public function refund(string $checkoutId, Money $amount): array
{
    return $this->send('POST', '/checkouts/' . rawurlencode($checkoutId) . '/refunds', [
        'amount' => (int) $amount->getAmount(),
        'currency' => $amount->getCurrency()->getCode(),
    ]);
}
```

Then list the handler in the gateway's `handlers()`:

```php
public function handlers(): array
{
    return [
        CaptureHandler::class,
        RefundHandler::class,
    ];
}
```

That is the whole mapping. Payum reads which handler interface the class implements and takes the
command from that interface's `handle()` signature. The gateway now has `Capability::Refund` too.

PHP does not let one class declare `handle()` twice, so a handler serves exactly one command. Listing two
handlers for the same command, or a class that implements no handler interface, throws a
`Payum\Core\Exception\LogicException` when the application calls `getPayum()`.

## The constructor

Take what lives as long as the gateway: the API class, a logger, the clock, a storage. Payum autowires
the handler, so any class or service the gateway's container can resolve works here. See
[Services](services.md) for the list and for declaring the ones autowiring cannot build.

Do not take the payment, the token or the HTTP request. They belong to one execution and come on the
context.

## The context

`Payum\Core\Handler\Context` carries everything about this one execution:

| Method | Returns |
| :--- | :--- |
| `amount()` | How much this execution is for, as a `Money` |
| `state()` | The gateway's saved data for this payment, as an `ArrayObject` |
| `subject()` | What the command acts on: loaded from the token, or the model the command carries |
| `payment()` | The subject when it is a payment, otherwise null |
| `payout()` | The subject when it is a payout, otherwise null |
| `token()` | The token this execution arrived on, if any |
| `httpRequest()` | The inbound HTTP request, as a PSR-7 `ServerRequestInterface` |
| `notifyUrl()` | A URL the PSP can send webhooks about this payment to. See [Webhooks](webhooks.md) |
| `notifyToken()` | The token behind `notifyUrl()` |
| `tokens()` | The `TokenIssuerInterface`, for creating other tokens |
| `gatewayName()` | The name the application registered the gateway under |
| `gateway()` | The gateway class instance, for its name and logo |
| `command()` | The command being handled |
| `execute()` | Dispatches another command on this gateway. See [Commands](commands.md) |
| `previous()` | The executions this one is nested in, when a handler dispatched it |

Read the amount from `amount()`, never from `$command->amount`. A full capture carries no amount on the
command, so reading the command charges nothing. `amount()` falls back to the subject's total and returns
a `Money` either way. See [Money and currencies](../money.md).

`NotifyHandlerInterface` is the one interface that differs: it has a `verify()` method, and its
`handle()` takes the verified event between the command and the context. See [Webhooks](webhooks.md).

## Capture runs more than once

Read this before writing a gateway that redirects the customer.

The PSP sends the customer back to the capture token's own URL, the same URL that started the capture.
Payum verifies the token again and dispatches the same `CaptureCommand` a second time. The handler
tells the two runs apart by reading the state it wrote during the first:

```php
public function handle(CaptureCommand $command, Context $context): CaptureResult
{
    $state = $context->state();

    // Second run: a checkout exists, so the customer has been to Acme and come back.
    if ($state['checkout_id']) {
        $checkout = $this->api->retrieveCheckout($state['checkout_id']);

        return 'paid' === $checkout['status']
            ? CaptureResult::captured($checkout['charge_id'], raw: $checkout)
            : CaptureResult::pending(raw: $checkout);
    }

    // First run: open a checkout and send the customer to it.
    $amount = $context->amount();

    $checkout = $this->api->createCheckout([
        'amount' => (int) $amount->getAmount(),
        'currency' => $amount->getCurrency()->getCode(),
        'return_url' => $context->token()->getTargetUrl(),
    ]);

    $state['checkout_id'] = $checkout['id'];

    return CaptureResult::pending(new Redirect($checkout['url']));
}
```

Payum does not track which run it is. Some PSPs need one run, some need three, and a 3-D Secure check
can add one at any point, so the handler decides from its state. The full handler is in
[Build a gateway](../develop-gateway-with-payum.md#take-the-payment).

The same applies to authorize and payout when the PSP needs the customer or an approver to act before
the operation finishes.

## State

`$context->state()` is the data your gateway needs to keep between HTTP requests for one payment, such
as the PSP's checkout id. It is a `Payum\Core\Bridge\Spl\ArrayObject` over the payment's details:

```php
$state = $context->state();

$state['checkout_id'];               // null for a missing key, no isset() needed
$state['checkout_id'] = 'chk_1';     // Payum sees the change, there is nothing to save
$state->defaults(['locale' => 'en']);
$state->validateNotEmpty(['checkout_id']);
$state->toUnsafeArray();             // a plain array with sensitive values unwrapped
```

Payum writes the state back onto the payment after the handler returns. When the command arrived on a
token, Payum also saves the payment to its storage. A payment the application passed to the command
itself is the application's to save.

Payum writes the state even when the handler throws. A checkout id stored before a failure has to
survive, or the retry opens a second checkout and the customer can pay twice.

`state()` throws a `LogicException` when there is no subject, which happens on a notify command that
points at nothing.

{% hint style="warning" %}
Keep card data and other secrets wrapped in `Payum\Core\Security\SensitiveValue` in the state. Call
`toUnsafeArray()` only to build the request you send to the PSP, never to store or log the state.
{% endhint %}

See [Sensitive information](../working-with-sensitive-information.md) for `SensitiveValue`.

## Report, do not write

A handler never sets the payment's status or amount. It returns a result that says what happened, and
Payum records the status on the payment. See [Results](results.md).

## Create a token

`notifyUrl()` covers the usual case. For anything else, such as a second page the customer must visit,
create a token with `tokens()`:

```php
use Payum\Core\Security\TokenPurpose;

$authorizeToken = $context->tokens()->create(
    TokenPurpose::Authorize,
    $context->gatewayName(),
    $context->subject(),
    $context->token()?->getAfterUrl(),
);

$authorizeToken->getTargetUrl();
```

The purposes are `Authorize`, `Capture`, `Refund`, `Payout` and `Notify`, and each one points at the
script the application set up for it. `gatewayName()` is null for a gateway built without
`PayumBuilder::registerGateway()`. See [Tokens and payment URLs](../tokens.md).
