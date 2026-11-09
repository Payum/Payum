# Commands

A command is what the caller wants a gateway to do, such as capture a payment or refund it. It is an
immutable object with no services on it. A gateway supports a command by shipping the handler for it.

## The commands

| Command | Capability | Handler returns | Also carries |
| :--- | :--- | :--- | :--- |
| `Payum\Core\Command\CaptureCommand` | `Capture` | `CaptureResult` | `amount`, `idempotencyKey` |
| `Payum\Core\Command\AuthorizeCommand` | `Authorize` | `AuthorizeResult` | `amount`, `idempotencyKey` |
| `Payum\Core\Command\RefundCommand` | `Refund` | `RefundResult` | `amount`, `reason`, `idempotencyKey` |
| `Payum\Core\Command\CancelCommand` | `Cancel` | `CancelResult` | `reason`, `idempotencyKey` |
| `Payum\Core\Command\PayoutCommand` | `Payout` | `PayoutResult` | `idempotencyKey` |
| `Payum\Core\Command\SyncCommand` | `Sync` | `SyncResult` | |
| `Payum\Core\Command\NotifyCommand` | `Webhooks` | `NotifyResult` | |

Capture takes the money. Authorize holds it on the customer's card without taking it, and a later
capture takes what was held. Refund gives back money already taken. Cancel calls a payment or payout off
before the money moves, such as voiding an authorization, so it carries no amount. Payout sends money
to a recipient. Sync asks the PSP for the current state. Notify carries a message the PSP sent.

## How a command is built

Every command needs to know what it acts on. It gets that from a token (when it arrives over HTTP) or
from the model itself (when the application calls it directly):

```php
<?php

use Money\Money;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;

// Arrived over HTTP, on a capture token.
CaptureCommand::forToken($token);

// Called from the application. No token was ever created.
CaptureCommand::forPayment($payment);

// A partial capture. An int is in minor units of the payment's currency.
CaptureCommand::forToken($token, amount: Money::EUR(500));
CaptureCommand::forToken($token, amount: 500);

// A key the PSP can use to recognise a retry, so the customer is not charged twice.
CaptureCommand::forPayment($payment, idempotencyKey: 'order-1001-capture');

// A partial refund, with a reason some PSPs record.
RefundCommand::forPayment($payment, amount: 500, reason: 'damaged_on_arrival');

// The same refund, safe to retry.
RefundCommand::forPayment($payment, amount: 500, reason: 'damaged_on_arrival', idempotencyKey: 'refund-77');

CancelCommand::forPayment($payment, reason: 'out_of_stock');
```

A command given neither a token nor a model throws a `Payum\Core\Exception\LogicException`. The one
exception is `NotifyCommand`, covered below.

Your handler does not build these. The application does, and the handler reads them. The application
side is covered in [Payment operations](../payment-operations.md).

## What a command acts on

Most commands act on a payment. `PayoutCommand` acts on a payout, and `CancelCommand` and `SyncCommand`
act on either. Both implement `Payum\Core\Model\SubjectInterface`, which is all Payum needs:

```php
PayoutCommand::forPayout($payout);

CancelCommand::forPayment($payment);
CancelCommand::forPayout($payout);

SyncCommand::forPayment($payment);
SyncCommand::forPayout($payout);
```

A command that arrived on a token carries no model, only the token. Payum loads the model the token
points at before your handler runs, so read it from the context rather than from the command:

```php
$context->subject();   // the payment or payout, whichever it is
$context->payment();   // null when the subject is not a payment
$context->payout();    // null when the subject is not a payout
```

## Read the amount from the context

`CaptureCommand`, `AuthorizeCommand` and `RefundCommand` take an optional amount. Null means the full
amount, so a full capture carries no amount at all.

Read the amount with `$context->amount()`. It returns what the caller asked for, or the subject's full
amount when they asked for nothing, as a `Money` in the subject's currency:

```php
$amount = $context->amount();       // Money, or null when the subject has no currency

$amount->getAmount();               // '1000', minor units as a string
$amount->getCurrency()->getCode();  // 'EUR'
```

{% hint style="warning" %}
Do not read `$command->amount` to decide how much to charge. It is null on a full capture, so a handler
that sends it to the PSP charges nothing.
{% endhint %}

See [Money and currencies](../money.md) for converting a `Money` to the format a PSP expects.

## Read the rest of a command

The other values are public properties or methods:

```php
$command->token();            // ?TokenInterface, set when the command came in over HTTP
$command->idempotencyKey;     // ?string, on every command except Sync and Notify
$command->reason;             // ?string, on Refund and Cancel
CaptureCommand::capability(); // Capability::Capture
```

Pass `idempotencyKey` to the PSP when it accepts one. Pass `reason` when the PSP records one.

## Sync

`SyncCommand` asks the PSP what state a payment or payout is in and records the answer. Applications
use it for a "refresh" button or a reconciliation job, or to catch up after a webhook that never
arrived. It changes nothing at the PSP, so it carries no idempotency key.

Not every PSP can report a payment's state, which is why `Capability::Sync` is a capability like any
other. Ship a sync handler only when yours can.

Inside your own handlers, call the API class directly when you need fresh data. Dispatching a
`SyncCommand` to fetch something you use on the next line adds a round trip through the middleware for
nothing.

## Notify

`NotifyCommand` carries a message the PSP sent to say something happened. It is the one command that
can point at nothing, because which payment a message is about is only known once it has been read:

```php
NotifyCommand::forToken($token);      // arrived on a notify token, the usual case
NotifyCommand::forPayment($payment);  // the application already knows the payment
NotifyCommand::forGateway();          // the application routed the endpoint itself
```

It sends nothing to the PSP, so it carries no idempotency key. See [Webhooks](webhooks.md) for its
handler.

## Dispatch a command from a handler

A handler can dispatch another command on the same gateway through the context. This notify handler
asks the PSP for the real state instead of trusting an unsigned message:

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

`execute()` returns the base `Payum\Core\Result\Result` type. A command dispatched from a handler runs
through the same middleware as the outer one, and Payum stops a chain of dispatches that is 100 levels
deep with a `LogicException`, which catches a handler that dispatches itself in a loop.

## A command the gateway does not handle

When a gateway has no handler for a command, `execute()` throws a
`Payum\Core\Exception\CommandNotSupportedException`. Its message names the gateway and says what to add:

```
Gateway "acme" (Acme Payments) does not handle Payum\Core\Command\RefundCommand. It handles
Payum\Core\Command\CaptureCommand. Add a handler for Payum\Core\Command\RefundCommand to
Acme\Payum\AcmeGateway::handlers().
```

An application that wants to check first calls `supportsCommand()`:

```php
if ($gateway->supportsCommand(RefundCommand::class)) {
    $gateway->execute(RefundCommand::forPayment($payment));
}
```

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
