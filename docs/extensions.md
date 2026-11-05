# Extensions

An extension is a class that a gateway calls before and after every command it executes. Payum still
runs extensions, but for new code two other tools do the job better:

* To watch payments, for an audit trail, receipts or alerts, listen to [events](events.md). One
  listener covers every gateway.
* To change what a command does, such as stopping it, retrying it or changing its result, write a
  [middleware](gateways/middleware.md). You register it once on the builder and it wraps every gateway.

Use an extension when you already have one and want it to keep running. This page describes what an
extension can and cannot do on a gateway that executes commands.

## Write an extension

Implement `Payum\Core\Extension\ExtensionInterface` and its three methods. Leave a method empty if you
have nothing to do at that point:

```php
<?php

declare(strict_types=1);

namespace App\Payments;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Psr\Log\LoggerInterface;

final class AuditExtension implements ExtensionInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
        $this->logger->info('Executing {command}', [
            'command' => get_debug_type($context->getRequest()),
        ]);
    }

    public function onPostExecute(Context $context): void
    {
        if (null !== $context->getException()) {
            $this->logger->error('{command} failed: {message}', [
                'command' => get_debug_type($context->getRequest()),
                'message' => $context->getException()->getMessage(),
            ]);
        }
    }
}
```

For each command, the gateway calls `onPreExecute()` and `onExecute()` one after the other, then runs
the handler, then calls `onPostExecute()`. `onPostExecute()` also runs when the handler throws, with the
exception available from `getException()`.

The `Context` gives you:

| Method | Returns |
| :--- | :--- |
| `getGateway()` | The gateway executing the command |
| `getRequest()` | The command, for example a `CaptureCommand` |
| `getException()` | The exception the command threw, or `null` |
| `getPrevious()` | The contexts of the commands this one runs inside, outermost first. Empty for the command your code executed |

`getAction()` always returns `null`, because a handler runs the command.

## Add it to a gateway

Extensions belong to one gateway object, and there is no builder method for them. Add yours to the
gateway after `getPayum()`:

```php
<?php

use Acme\Payum\AcmeConfig;
use App\Payments\AuditExtension;
use Payum\Core\Gateway;
use Payum\Core\PayumBuilder;

/** @var \Psr\Log\LoggerInterface $logger */

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();

$gateway = $payum->getGateway('acme');

if ($gateway instanceof Gateway) {
    $gateway->addExtension(new AuditExtension($logger));
}
```

`getGateway()` is typed as `Payum\Core\GatewayInterface`, which has no `addExtension()`, so check for
`Payum\Core\Gateway` first. It returns the same object on every call, so add the extension once.

Extensions run in the order you add them. Pass `true` as the second argument to put one in front of the
others.

## What an extension cannot do

An extension can stop a command by throwing an exception from `onPreExecute()` or `onExecute()`. The
handler does not run and the exception reaches your code.

It cannot change the outcome of a command:

* `setReply()` has no effect. Your code receives the handler's result.
* `setException(null)` has no effect. The exception still reaches your code.
* `setAction()` has no effect. The gateway always runs the command's handler.

To change a result, retry a command or swallow an exception you have handled, write a
[middleware](gateways/middleware.md). A middleware receives the command and returns the result, so it
can change both.
