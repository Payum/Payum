# Middleware

Middleware is code that runs around every command a gateway handles. It sees the command going in and
the result coming out, and it can stop the command or change what is passed on. Use it for logging,
locking, idempotency or a guard that applies to many commands.

## Write a middleware

Implement `Payum\Core\Middleware\MiddlewareInterface`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\Result;
use Psr\Log\LoggerInterface;

final class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $this->logger->info('Dispatching {command}', ['command' => $command::class]);

        $result = $next($command, $context);

        $this->logger->info('Got {status}', ['status' => $result->status?->value ?? 'no status']);

        return $result;
    }
}
```

Call `$next` to continue. Return without calling it and the handler never runs, which is how a guard or
a cache works. Wrap the call in `try`/`finally` for anything that must happen even when the handler
throws.

`$next` takes a command and a context, so a middleware can pass on a different command. Commands are
immutable, so build a new one to change it.

To only watch what happens, for an audit trail or analytics, listen to an [event](../events.md)
instead. Middleware is for code that changes what happens.

## Register middleware for every gateway

Most middleware has nothing to do with one gateway. Logging, locking and idempotency apply to every
command whoever handles it, so the application registers them once on the builder and they wrap every
gateway:

```php
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addMiddleware(new LoggingMiddleware($logger))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

`addMiddleware()` also takes a class name. Payum then builds the middleware from each gateway's
container, so its constructor is autowired like a handler's:

```php
->addMiddleware(LoggingMiddleware::class)
```

A package that ships middleware publishes the class and tells applications to register it this way.

## Order

A higher priority runs further out: first on the way in, last on the way out. Pass the priority as the
second argument:

```php
->addMiddleware(new OutermostMiddleware(), 1000)
->addMiddleware(new InnermostMiddleware(), -100)
```

Middleware with no priority sits at 0, and registration order breaks ties. A middleware can carry its
own default by implementing `Payum\Core\Middleware\HasPriority`:

```php
use Payum\Core\Middleware\HasPriority;

final class LoggingMiddleware implements MiddlewareInterface, HasPriority
{
    public static function priority(): int
    {
        return 200;
    }

    // ...
}
```

A priority passed to `addMiddleware()` wins over the one the class declares.

Payum registers these on every gateway, outermost first:

| Middleware | Priority | What it does |
| :--- | :--- | :--- |
| `TemplateRenderMiddleware` | 2000 | Adds the variables every template gets. See [Templates](templates.md) |
| `EndlessCycleDetectorMiddleware` | 1000 | Stops a chain of nested commands 100 levels deep |
| `LegacyExtensionMiddleware` | 500 | Runs the extensions registered on the gateway. See [Extensions](../extensions.md) |
| `PersistStateMiddleware` | 100 | Writes the handler's state onto the payment and saves it |
| `RecordPaymentStatusMiddleware` | 50 | Records the result's status on the payment |

Middleware at the default priority of 0 runs inside all five, closest to the handler.

When a handler throws, `PersistStateMiddleware` still writes the state, so a PSP id stored before the
failure survives. `RecordPaymentStatusMiddleware` records nothing, because the status is unknown. A
middleware of yours that runs inside both sees the payment before either has written to it.

## Middleware for one gateway

A gateway declares middleware of its own by implementing `Payum\Core\Gateway\DeclaresMiddleware`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Acme\Payum\Middleware\AcmeAuditMiddleware;
use Payum\Core\Gateway\DeclaresMiddleware;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresMiddleware
{
    public function middleware(): array
    {
        return [
            AcmeAuditMiddleware::class,
        ];
    }

    // name(), logo(), websiteUrl(), configClass() and handlers() as before
}
```

The entries are class names or container ids, built from the gateway's own container. At the same
priority they run inside the middleware the application registered.

Use this rarely. If the code would make sense for another gateway, it belongs on the builder. Getting an
access token from the PSP, for example, belongs in the API class, where it can be cached, not in
middleware: middleware would run it for commands that never call the PSP.
