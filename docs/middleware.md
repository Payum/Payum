# Middleware

Middleware is code that runs around every command a gateway handles: capture, refund, sync, notify and
the rest. It sees the command going in and the result coming out, and it can stop a command before the
gateway sees it. Use it for things that apply to every payment whichever gateway handles it, such as
logging, guards and audit records.

## Write a middleware

Implement `Payum\Core\Middleware\MiddlewareInterface`. This one logs every command and its outcome:

```php
<?php

declare(strict_types=1);

namespace App\Payment;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\Result;
use Psr\Log\LoggerInterface;

final class LogPaymentsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $result = $next($command, $context);

        $this->logger->info('{command} on {gateway}: {status}', [
            'command' => $command::class,
            'gateway' => $context->gatewayName(),
            'status' => $result->status?->value ?? 'no status',
        ]);

        return $result;
    }
}
```

`$next($command, $context)` runs the rest of the pipeline and the handler, and returns the handler's
result. Code before the call runs before the gateway does anything, and code after it sees the result.

`$result->status` is null when the command concluded nothing about the payment, for example a declined
refund, so always handle the null case.

## Register a middleware

Register middleware on the builder. It then wraps every command on every gateway:

```php
<?php

use App\Payment\LogPaymentsMiddleware;
use Payum\Core\PayumBuilder;
use Psr\Log\LoggerInterface;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(LoggerInterface::class, $logger)
    ->addMiddleware(LogPaymentsMiddleware::class)
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Passing the class name lets Payum build the middleware from the gateway's container, so its constructor
arguments are autowired. Here the logger comes from `addGlobalService()`. You can also pass an instance
you built yourself:

```php
->addMiddleware(new LogPaymentsMiddleware($logger))
```

A middleware package publishes its class and tells you to register it the same way.

## Read the command and the payment

The command says what was asked, and the context holds everything about this execution. These are the
parts of `Payum\Core\Handler\Context` a middleware usually needs:

| Method | Returns |
| :--- | :--- |
| `command()` | The command being executed |
| `gatewayName()` | The name the gateway was registered under, such as `'acme'` |
| `payment()` | The payment, or null when the command operates on a payout |
| `payout()` | The payout, or null when the command operates on a payment |
| `amount()` | The amount this command is for, as a `Money\Money`, or null |
| `token()` | The token the command came in on, or null |
| `httpRequest()` | The incoming PSR-7 request |

Check the command's class to act on one kind of command only:

```php
use Payum\Core\Command\RefundCommand;

if ($command instanceof RefundCommand) {
    // ...
}
```

## Stop a command

Return or throw without calling `$next`, and the gateway never sees the command. This guard refuses
refunds above a limit:

```php
<?php

declare(strict_types=1);

namespace App\Payment;

use Money\Money;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\Result;
use RuntimeException;

final class RefundLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Money $limit,
    ) {
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $amount = $context->amount();

        if ($command instanceof RefundCommand
            && $amount instanceof Money
            && $amount->isSameCurrency($this->limit)
            && $amount->greaterThan($this->limit)
        ) {
            throw new RuntimeException(sprintf('Refunds above %s need a manual review.', $this->limit->getAmount()));
        }

        return $next($command, $context);
    }
}
```

Register it with the limit you want:

```php
use Money\Money;

->addMiddleware(new RefundLimitMiddleware(Money::EUR(50000)))
```

The exception reaches the code that executed the command, and nothing was sent to the PSP. A middleware
can also return a result of its own instead of throwing, which is how a cache would answer a command
without the gateway.

To run code even when the handler throws, wrap the call in `try`/`finally`:

```php
try {
    return $next($command, $context);
} finally {
    // runs whether the handler returned or threw
}
```

## Choose the order

Middleware with a higher priority runs further out: first on the way in, last on the way out. Pass the
priority as the second argument to `addMiddleware()`:

```php
->addMiddleware(RefundLimitMiddleware::class, 1500)
->addMiddleware(LogPaymentsMiddleware::class)
```

Middleware with no priority sits at 0, and the order you register it in breaks ties. A middleware class
can carry its own default by implementing `Payum\Core\Middleware\HasPriority`:

```php
use Payum\Core\Middleware\HasPriority;

final class RefundLimitMiddleware implements MiddlewareInterface, HasPriority
{
    public static function priority(): int
    {
        return 1500;
    }

    // ...
}
```

A priority passed to `addMiddleware()` wins over the one the class declares.

## Middleware Payum registers

Payum registers these on every gateway. You do not register them yourself:

| Middleware | Priority | What it does |
| :--- | :--- | :--- |
| `TemplateRenderMiddleware` | 2000 | Adds the gateway, the subject, the token and the command to every template a gateway renders. See [Customize payment pages](payment-pages.md) |
| `EndlessCycleDetectorMiddleware` | 1000 | Stops a chain of nested commands 100 levels deep |
| `LegacyExtensionMiddleware` | 500 | Runs the extensions registered on the gateway |
| `PersistStateMiddleware` | 100 | Writes the gateway's state onto the payment and saves it |
| `RecordPaymentStatusMiddleware` | 50 | Records the result's status on a payment that implements `StatusAwareInterface`. See [Payment status](payment-status.md) |

All five are in the `Payum\Core\Middleware` namespace. Your middleware at the default priority of 0 runs
inside all of them, closest to the handler. It therefore sees the payment before Payum has saved the
state or recorded the status. Give it a priority above 100 to run outside them and see the payment after
both.

## Middleware you can add

Payum ships middleware that it does not register for you:

| Middleware | Priority | What it does |
| :--- | :--- | :--- |
| `LockingMiddleware` | 900 | Lets one command at a time work on a payment, so a double click or a webhook racing the customer's return cannot capture twice. Needs `symfony/lock`. See [Concurrent requests and idempotency](concurrency-and-idempotency.md) |

## Middleware or an event

Payum also dispatches PSR-14 events at each step of a command. Use an event listener when you only want
to know what happened, for an audit trail, a notification email or metrics. Use middleware when you need
to change what happens: stop a command, retry it, or wrap it in a lock or a transaction. See
[Events](events.md) for the events and how to listen to them.

## Advanced: middleware for one gateway

A gateway package can ship middleware that only runs for that gateway. It declares it on the gateway
class, and you do not register it. If you write gateways, see [Gateway middleware](gateways/middleware.md).

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
