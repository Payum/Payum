# Logging

When a payment goes wrong, a log of what Payum did is the quickest way to find out why. Payum works with
any [PSR-3](https://www.php-fig.org/psr/psr-3/) logger, such as Monolog. Payum itself writes no log
lines. You register your logger once, then log from a middleware or an event listener, and any gateway
code that asks for a logger receives yours.

## Register your logger

Register the logger as a global service under `Psr\Log\LoggerInterface`:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;
use Psr\Log\LoggerInterface;

/** @var LoggerInterface $logger */

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(LoggerInterface::class, $logger)
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Payum shares every global service with every gateway. A handler or middleware that takes a
`LoggerInterface` constructor argument now receives your logger. See
[Replace and add services](di/customization.md) for more on global services.

## Log every command

A middleware runs around every command, so it is the place to log what each command did. This one logs
the command when it starts, and its status or its exception when it ends:

```php
<?php

declare(strict_types=1);

namespace App\Payments;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\Result;
use Psr\Log\LoggerInterface;
use Throwable;

final class LogCommandsMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $details = [
            'gateway' => $context->gatewayName(),
            'command' => $command::class,
        ];

        $this->logger->info('{gateway}: {command} started', $details);

        try {
            $result = $next($command, $context);
        } catch (Throwable $exception) {
            $this->logger->error('{gateway}: {command} failed', $details + ['exception' => $exception]);

            throw $exception;
        }

        $this->logger->info('{gateway}: {command} finished with status {status}', $details + [
            'status' => $result->status?->value ?? 'none',
            'failure' => $result->failure?->reason->value,
        ]);

        return $result;
    }
}
```

Register it on the builder by class name:

```php
->addMiddleware(LogCommandsMiddleware::class)
```

Payum creates the middleware from the container and passes it the logger you registered. Always rethrow
the exception after you log it. A middleware that swallows it hides a failed payment from your code.

See [Middleware](middleware.md) for ordering and the other things middleware can do.

## Log from an event listener

If you only need certain events, such as each failure or each change of status, a listener is shorter
than a middleware:

```php
<?php

use Payum\Core\Event\StatusChanged;

$dispatcher->addListener(StatusChanged::class, function (StatusChanged $event) use ($logger): void {
    $logger->info('Payment {number} is now {status}', [
        'number' => $event->context->payment()?->getNumber(),
        'status' => $event->to->value,
    ]);
});
```

`$dispatcher` is the PSR-14 dispatcher you registered with Payum. See [Events](events.md) for the list
of events and how to register a dispatcher.

## Keep card data out of your logs

{% hint style="warning" %}
Never log a card number, a security code or a raw request to the payment provider. Log the gateway,
the command, the payment number and the status, as the samples above do.
{% endhint %}

See [Sensitive information](working-with-sensitive-information.md) for how Payum keeps card data out of
storage and debug output.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
