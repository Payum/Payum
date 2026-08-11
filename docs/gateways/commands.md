# Commands

A command is what the caller wants done. It is immutable, carries no services, and names the capability it exercises.

### Available commands

| Command | Capability | Result | Extra |
| :--- | :--- | :--- | :--- |
| `Payum\Core\Command\CaptureCommand` | `Capture` | `CaptureResult` | `amount`, `idempotencyKey` |
| `Payum\Core\Command\AuthorizeCommand` | `Authorize` | `AuthorizeResult` | `amount`, `idempotencyKey` |
| `Payum\Core\Command\RefundCommand` | `Refund` | `RefundResult` | `amount`, `reason`, `idempotencyKey` |
| `Payum\Core\Command\CancelCommand` | `Cancel` | `CancelResult` | `reason`, `idempotencyKey` |
| `Payum\Core\Command\PayoutCommand` | `Payout` | `PayoutResult` | `idempotencyKey` |
| `Payum\Core\Command\SyncCommand` | `Sync` | `SyncResult` | — |

### Building one

Every command needs a subject — either the token it arrived on, or the payment directly:

```php
<?php
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;

// Came in over HTTP, on a capture token.
CaptureCommand::forToken($token);

// Headless. No token was ever minted.
CaptureCommand::forPayment($payment);

// Partial capture. The amount is in minor units, as on PaymentInterface.
CaptureCommand::forToken($token, amount: 500);

// An idempotency key, for PSPs that accept one, so a retry cannot double-charge.
CaptureCommand::forToken($token, idempotencyKey: $order->getUuid());

// Partial refund with a reason the PSP will record.
RefundCommand::forPayment($payment, amount: 500, reason: 'damaged_on_arrival');

// Void an authorization the merchant has decided not to settle.
CancelCommand::forPayment($payment, reason: 'out_of_stock');
```

Cancel calls it off before the money moves — voiding an authorization, abandoning a payment the customer never completed, or stopping a payout that has not gone out. It is not a refund, which gives back money already taken, and it carries no amount because it is all or nothing.

### Refreshing from the PSP

`SyncCommand` asks the PSP what it currently thinks and records the answer, which is how a stored status catches up after a webhook that never arrived:

```php
$result = $gateway->execute(SyncCommand::forPayment($payment));

$result->status;   // whatever the PSP says
```

It is for callers — an admin screen with a refresh button, a reconciliation job. A handler that needs fresh data mid-flow calls its api directly instead; dispatching a command to fetch something it uses two lines later is indirection for its own sake.

It reads rather than mutates, so it takes no idempotency key. Not every PSP offers a way to re-read, which is why `Capability::Sync` is a capability like any other rather than something assumed.

### What a command operates on

Every command acts on a **subject**: a payment for most of them, a payout for `PayoutCommand`. Both implement `Payum\Core\Model\SubjectInterface`, which is all core needs — details it can read and write, and a class to look a storage up by.

```php
// a payout is not a payment
PayoutCommand::forPayout($payout);

// cancelling does not care which it is
CancelCommand::forPayment($payment);
CancelCommand::forPayout($payout);
```

The subject on the command is **what the caller supplied**, which is null whenever the command carries only a token — the usual case for anything arriving over HTTP. Core resolves the real one from the token's identity, so a handler reads it from the context instead:

```php
$context->subject();   // whatever it is
$context->payment();   // null when it is not a payment
$context->payout();    // null when it is not a payout
```

Give it neither a token nor a payment and the constructor throws — it has to know what it is operating on.

### Dispatching

```php
<?php
$gateway = $payum->getGateway('acme');

$result = $gateway->execute(CaptureCommand::forToken($token));
```

If the gateway declares no handler for that command you get a `Payum\Core\Exception\CommandNotSupportedException`:

```
Gateway "acme_eu" (Acme Payments) does not handle Payum\Core\Command\RefundCommand.
It handles Payum\Core\Command\CaptureCommand. Add a handler for
Payum\Core\Command\RefundCommand to Acme\Payum\AcmeGateway::handlers().
```

It names the gateway by the name it is registered under, which is what you need when the gateway is picked at runtime, and the same details are readable off the exception:

```php
use Payum\Core\Exception\CommandNotSupportedException;

try {
    $gateway->execute(RefundCommand::forToken($token));
} catch (CommandNotSupportedException $e) {
    $e->getGatewayName();       // 'acme_eu'
    $e->getGatewayClass();      // Acme\Payum\AcmeGateway
    $e->getSupportedCommands(); // the commands it does handle
    $e->getCommand();           // the one you dispatched
}
```

To branch without catching, ask first:

```php
if ($gateway->supportsCommand(RefundCommand::class)) {
    $gateway->execute(RefundCommand::forToken($token));
}
```

### From a handler

A handler dispatches a sub-command through the context rather than holding the gateway:

```php
public function handle(CaptureCommand $command, Context $context): CaptureResult
{
    $result = $context->execute(RefundCommand::forPayment($context->payment()));
}
```

### Reading a command

```php
$command->token();          // ?TokenInterface
$command->payment();        // ?PaymentInterface
$command->amount;           // ?int, minor units. Null means the payment's full amount
$command->idempotencyKey;   // ?string
CaptureCommand::capability(); // Capability::Capture
```

Amounts are integers in minor units, matching `PaymentInterface::getTotalAmount()`. A null amount means "the whole payment", which is not the same as zero.

Next: [Handlers](handlers.md).
