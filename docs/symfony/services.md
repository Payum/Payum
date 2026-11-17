# Use your services in Payum

Payum builds each gateway with a container of its own. PayumBundle connects that container to Symfony's,
so a gateway can use your application's services and Payum's events reach your Symfony listeners. This
page shows which services Payum sees, how to share more, and how to replace one of Payum's defaults.

## Services Payum gets from Symfony

When your application has them, the bundle hands these services to Payum without any configuration:

| Payum asks for | Symfony service |
| :--- | :--- |
| `Psr\Log\LoggerInterface` | `logger` |
| `Psr\EventDispatcher\EventDispatcherInterface` | `event_dispatcher` |
| `Symfony\Contracts\EventDispatcher\EventDispatcherInterface` | `event_dispatcher` |
| `Symfony\Component\Routing\Generator\UrlGeneratorInterface` | `router` |
| `Twig\Environment` | `twig` |

Payum provides its own defaults for everything else a gateway needs, such as the PSR-18 HTTP client and
the clock. [How dependency injection works](../di/how-it-works.md#services-shared-by-every-gateway) lists
them.

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

A service you share wins over Payum's own default for the same id. For example, Payum keeps idempotency
records in memory unless you give it a store. Share a store that writes them to your database:

```yaml
# config/services.yaml
services:
    Payum\Core\Bridge\Doctrine\Idempotency\DbalIdempotencyStore:
        arguments:
            $connection: '@doctrine.dbal.default_connection'
        tags:
            - { name: payum.global_service, id: Payum\Core\Idempotency\IdempotencyStoreInterface }
```

An idempotency record lets Payum answer a repeated command with the first result instead of charging the
customer twice. [Concurrent requests and idempotency](../concurrency-and-idempotency.md#store-the-records)
explains the stores and the table they need.

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
