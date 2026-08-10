# Commands

A command is what the caller wants done. It is immutable, carries no services, and names the capability it exercises.

### Available commands

| Command | Capability | Result | Extra |
| :--- | :--- | :--- | :--- |
| `Payum\Core\Command\CaptureCommand` | `Capture` | `CaptureResult` | `amount`, `idempotencyKey` |
| `Payum\Core\Command\AuthorizeCommand` | `Authorize` | `AuthorizeResult` | `amount`, `idempotencyKey` |
| `Payum\Core\Command\RefundCommand` | `Refund` | `RefundResult` | `amount`, `reason`, `idempotencyKey` |

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
```

Give it neither a token nor a payment and the constructor throws — it has to know what it is operating on.

### Dispatching

```php
<?php
$gateway = $payum->getGateway('acme');

$result = $gateway->execute(CaptureCommand::forToken($token));
```

If the gateway declares no handler for that command you get a `Payum\Core\Exception\CommandNotSupportedException`:

```php
use Payum\Core\Exception\CommandNotSupportedException;

try {
    $gateway->execute(RefundCommand::forToken($token));
} catch (CommandNotSupportedException $e) {
    // $e->getCommand()
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
