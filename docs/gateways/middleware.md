# Middleware

Middleware wraps the execution of a command. It runs around the handler rather than instead of it, so it sees the command going in and the result coming back.

```php
<?php
namespace Acme\Payum\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\Result;
use Psr\Log\LoggerInterface;

final class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $this->logger->info('Dispatching {command}', ['command' => $command::class]);

        $result = $next($command, $context);

        $this->logger->info('Got {status}', ['status' => $result->status->value]);

        return $result;
    }
}
```

Call `$next` to continue. Return without calling it and the handler never runs, which is how a guard or a cache would work. Wrap it in `try`/`finally` for anything that has to happen even when the handler throws.

If you only want to watch — audit trails, receipts, analytics, your own outbound webhooks — listen to an [event](../events.md) instead. Middleware is for the concerns that need to change what happens.

`$next` takes a command and a context, so middleware can pass different ones on — a command is immutable, so this is how you would amend one.

### Registering it

Most middleware has nothing to do with any particular gateway. Logging, locking and idempotency apply to every command whoever handles it, so they belong on the builder, where they are registered once and wrap every gateway:

```php
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addMiddleware(new LoggingMiddleware($logger))
    ->registerGateway('acme', new AcmeConfig('sk_…'))
    ->getPayum();
```

You can register a container id instead of an instance, and it is resolved from the gateway's container when the pipeline is built:

```php
->addMiddleware(LoggingMiddleware::class)
```

### Order

Higher priority runs further out: first on the way in, last on the way back.

```php
->addMiddleware(new OutermostMiddleware(), 1000)
->addMiddleware(new InnermostMiddleware(), -100)
```

Middleware that does not say otherwise sits at 0, and registration order breaks ties. A middleware can carry its own default instead of relying on whoever registers it:

```php
use Payum\Core\Middleware\HasPriority;

final class LoggingMiddleware implements MiddlewareInterface, HasPriority
{
    public static function priority(): int
    {
        return 200;
    }
}
```

An explicit priority passed to `addMiddleware()` overrides that.

What core registers, outermost first:

| Middleware | Priority | Does |
| :--- | :--- | :--- |
| `TemplateRenderMiddleware` | 2000 | Fills in the variables every template gets |
| `EndlessCycleDetectorMiddleware` | 1000 | Stops a handler that dispatches its way into a loop |
| `LegacyExtensionMiddleware` | 500 | Runs the gateway's registered extensions |
| `PersistStateMiddleware` | 100 | Writes the PSP state back onto the payment |
| `RecordPaymentStatusMiddleware` | 50 | Commits the status the handler declared, when the payment tracks one |

So anything you register at the default 0 runs inside all five, closest to the handler.

Two of them behave deliberately differently when a handler throws, and it is worth knowing which is which. `PersistStateMiddleware` writes anyway — a PSP token recorded just before a failure has to survive, or the retry opens a second checkout. `RecordPaymentStatusMiddleware` writes nothing, because an exception means nobody learned what the status is.

`TemplateRenderMiddleware` is why a handler returning `new RenderTemplate('@PayumAcme/checkout.html.twig')` with no context still gets a template that can name the gateway and post a form back to the token. On the way out it adds `gateway`, `subject`, `token`, `command` and `context` to any `RenderTemplate` result, leaving anything the handler passed under the same name alone — see [Templates](templates.md). It is outermost so that every result on its way out passes through it, whichever handler or middleware produced it.

### Middleware belonging to one gateway

A gateway can declare its own, which is registered only for that gateway:

```php
<?php
use Payum\Core\Gateway\DeclaresMiddleware;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresMiddleware
{
    public function middleware(): array
    {
        return [AcmeAuditMiddleware::class];
    }
}
```

These are container ids, resolved from the gateway's own container, and they run inside anything the application registered.

Reach for this rarely. If the concern would make sense for another gateway, it belongs on the builder instead. Obtaining an access token, for instance, looks like a candidate but is not one: it is a property of talking to that PSP, not of executing a command, so it belongs in the api — cached there, or handled by a PSR-18 decorator around the HTTP client. Putting it in middleware would fire it for commands that never call the PSP, and tie authentication to dispatch.

### Shipping middleware in a package

Nothing about middleware is tied to a gateway, so a package can ship one and let the application register it. Publish a class implementing `MiddlewareInterface`, and either document `addMiddleware()` or have your framework integration call it.

### Extensions

Extensions registered on a gateway still run for commands, through `LegacyExtensionMiddleware`. The bridge is one-way: an extension observes the command and can abort by throwing, but it cannot swap the result — a `Result` is not a `ReplyInterface`, so `Context::setReply()` has nothing to accept — and `getAction()` is always null, because a command is answered by a handler.

New code should use middleware.
