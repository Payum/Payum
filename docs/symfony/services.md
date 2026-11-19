# Use your services in Payum

Payum builds each gateway with a container of its own. PayumBundle connects that container to Symfony's,
so a gateway can use your application's services and Payum's events reach your Symfony listeners. This
page shows which services Payum sees, how to share more, how to replace one of Payum's defaults, and how
to run your own code around every command.

## Services Payum gets from Symfony

When your application has them, the bundle hands these services to Payum without any configuration:

| Payum asks for | Symfony service |
| :--- | :--- |
| `Psr\Log\LoggerInterface` | `logger` |
| `Psr\EventDispatcher\EventDispatcherInterface` | `event_dispatcher` |
| `Symfony\Contracts\EventDispatcher\EventDispatcherInterface` | `event_dispatcher` |
| `Symfony\Component\Routing\Generator\UrlGeneratorInterface` | `router` |
| `Twig\Environment` | `twig` |
| `Psr\Http\Message\ServerRequestInterface` | The request Symfony is serving, converted to PSR-7 |
| `Psr\Http\Client\ClientInterface` | Symfony's PSR-18 client, when `symfony/http-client` is installed |
| `Psr\Http\Message\RequestFactoryInterface`, `ResponseFactoryInterface`, `StreamFactoryInterface` and `UriFactoryInterface` | The PSR-17 factories registered under these ids |
| `Psr\Clock\ClockInterface` | `clock`, when `symfony/clock` is installed |
| `Symfony\Component\Lock\LockFactory` | `lock.factory`, when `symfony/lock` is installed |
| `Payum\Core\Idempotency\IdempotencyStoreInterface` | The store set under `payum.idempotency` |

A gateway's handlers read the incoming request, for example a webhook's body and signature, from
`ServerRequestInterface`. The bundle always gives them the request Symfony is serving at that moment, so
a functional test and a long-running worker such as FrankenPHP or RoadRunner work like PHP-FPM.

Payum provides its own default for anything your application does not have.
[How dependency injection works](../di/how-it-works.md#services-shared-by-every-gateway) lists them.

## Share a service with Payum

Tag a service `payum.global_service` to make it available to every gateway:

```yaml
# config/services.yaml
services:
    App\Payment\ExchangeRates:
        tags:
            - { name: payum.global_service }
```

Payum knows the service under its service id, here the class name. A gateway's handler or action that
type-hints `App\Payment\ExchangeRates` in its constructor now gets your service.

When the class type-hints an interface, share the service under that interface with the `id` attribute:

```yaml
# config/services.yaml
services:
    App\Payment\ExchangeRates:
        tags:
            - { name: payum.global_service, id: App\Payment\ExchangeRatesInterface }
```

Only tagged services and the ones in the table above reach Payum. The rest of your container stays
hidden from it.

## Replace one of Payum's defaults

A service you tag wins over the one the bundle shares and over Payum's own default for the same id. For
example, freeze the time Payum sees in your tests by sharing Symfony's `MockClock` as the clock:

```yaml
# config/services_test.yaml
services:
    Symfony\Component\Clock\MockClock:
        arguments:
            $now: '2026-01-15 10:00:00'
        tags:
            - { name: payum.global_service, id: Psr\Clock\ClockInterface }
```

Payum reads the clock for anything that expires, such as idempotency records.

## Run code around every command

A middleware runs around every command any gateway handles: capture, refund, sync, notify and the rest.
Use one for logging, guards and audit records. Implement `Payum\Core\Middleware\MiddlewareInterface`
and add the `#[AsPayumMiddleware]` attribute:

```php
<?php
// src/Payment/LogPaymentsMiddleware.php

namespace App\Payment;

use Payum\Bundle\PayumBundle\Attribute\AsPayumMiddleware;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\Result;
use Psr\Log\LoggerInterface;

#[AsPayumMiddleware(priority: 150)]
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

With autoconfiguration on, the bundle registers every service whose class implements `MiddlewareInterface`, so
the attribute is only needed to set a priority. Middleware with a higher priority runs further out. At
the default of 0 it runs inside all of Payum's own middleware and sees the payment before Payum saves it.
The priority of 150 above runs it after Payum has saved the payment and recorded its status.
[Middleware](../middleware.md) lists Payum's middleware and their priorities.

Without autoconfiguration, tag the service `payum.middleware` instead, as
[Container tags](container-tags.md#payummiddleware) shows.

## Listen to Payum's events

Payum dispatches its events through Symfony's event dispatcher, so you listen to them like any Symfony
event. This listener sends a receipt when a payment is captured:

```php
<?php
// src/EventListener/SendReceipt.php

namespace App\EventListener;

use App\Payment\ReceiptMailer;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Result\PaymentStatus;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class SendReceipt
{
    public function __construct(
        private readonly ReceiptMailer $mailer,
    ) {
    }

    public function __invoke(StatusChanged $event): void
    {
        if (PaymentStatus::Captured === $event->to) {
            $this->mailer->send($event->subject);
        }
    }
}
```

With autoconfiguration on, `#[AsEventListener]` registers the listener for the event class its
`__invoke()` method type-hints. A listener can only watch. It cannot change the result of the command.
[Events](../events.md) lists every event and when Payum dispatches it.

## Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
