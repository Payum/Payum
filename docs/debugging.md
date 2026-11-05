# Debugging

When a payment does not end the way you expect, you need to know which commands ran, what each one
returned, and what the payment provider said. This page shows where Payum keeps that information and
how to get it out.

## Read the result

Every command returns a result. Before you look anywhere else, look at it:

```php
<?php

use Payum\Core\Command\CaptureCommand;

/** @var \Payum\Core\Payum $payum */
/** @var \Payum\Core\Model\PaymentInterface $payment */

$result = $payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

var_dump(
    $result->status,          // the payment's status after the command, or null
    $result->next,            // what the customer has to do next, or null when the command is finished
    $result->failure?->reason, // why it failed, such as FailureReason::InsufficientFunds
    $result->failure?->code,   // the provider's own error code
    $result->failure?->message, // the provider's own message
    $result->raw,             // the provider's own data, when the gateway passes it on
);
```

A declined card is not an exception. It comes back as a result with a `failure`, so a capture that
"does nothing" has usually failed and told you why. The provider's `message` is for you, not for the
customer. Review it before you show it to anyone. See [Payment operations](payment-operations.md) for
what each field means.

## See every command

A capture often runs other commands on its way, and a gateway may call the provider more than once.
Log each command with a middleware to see the whole sequence:

```php
->addMiddleware(LogCommandsMiddleware::class)
```

[Logging](logger.md) has the complete middleware and shows how to register your logger.

## Catch the exceptions Payum does not show you

Some entry points turn an exception into an HTTP response. `Payum::notify()`, for example, answers a
notification that fails verification with an empty `400` response and does not rethrow the exception.
Listen to `CommandFailed` to see those exceptions too:

```php
<?php

use Payum\Core\Event\CommandFailed;

/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
/** @var \Psr\Log\LoggerInterface $logger */

$dispatcher->addListener(CommandFailed::class, function (CommandFailed $event) use ($logger): void {
    $logger->error('{gateway}: {command} failed: {message}', [
        'gateway' => $event->context->gatewayName(),
        'command' => $event->command::class,
        'message' => $event->exception->getMessage(),
        'exception' => $event->exception,
    ]);
});
```

`CommandFailed` fires for every exception that leaves a command, before Payum rethrows it or turns it
into a response. See [Events](events.md) to register the dispatcher and for the other events.

## Look at what the gateway stored

A gateway keeps what it learns from the provider, such as the provider's own payment id, in the
payment's details. Payum writes them onto the payment after every command, even one that throws, and
saves the payment when the command ran from a token. Read them to see how far a payment got:

```php
var_dump($payment->getDetails());
```

The details are the gateway's own, so their keys differ between gateways. The gateway's documentation
lists them.

## Advanced: see how deep a command runs

A handler can run another command, and that command's handler can run a third. Inside a middleware,
`$context->previous()` returns the contexts of the commands the current one runs inside, outermost
first. It is empty for the command your code executed:

```php
$this->logger->debug('{command} at depth {depth}', [
    'command' => $command::class,
    'depth' => count($context->previous()),
]);
```

Payum stops a chain of commands that reaches 100 levels with a `Possible endless cycle detected`
exception. If you see that message, a handler keeps executing a command that leads back to itself. See
[Troubleshooting](troubleshooting.md) for this and other errors.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
